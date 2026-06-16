<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LessonSession;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Hiển thị danh sách đơn xin nghỉ và form tạo mới
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. Lấy tất cả đơn xin nghỉ của học viên hiện tại
        $requests = LeaveRequest::where('user_id', $userId)
            ->with(['lessonSession.courseClass', 'approver'])
            ->latest()
            ->get();

        // Phân loại đơn vào các tab tương ứng (Chấp nhận cả định dạng EN từ hệ thống TA nếu có)
        $draftRequests    = $requests->filter(fn($r) => in_array($r->status, ['Nháp', 'draft']));
        $pendingRequests  = $requests->filter(fn($r) => in_array($r->status, ['Chờ duyệt', 'pending']));
        $approvedRequests = $requests->filter(fn($r) => in_array($r->status, ['Đã duyệt', 'approved']));
        $rejectedRequests = $requests->filter(fn($r) => in_array($r->status, ['Từ chối', 'rejected']));

        // 2. Lấy danh sách các buổi học của các lớp mà học viên này tham gia để chọn trong Form
        $lessonSessions = LessonSession::whereHas('courseClass.users', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with('courseClass')
            ->orderBy('lesson_date', 'desc')
            ->get();

        return view('student.leave_requests.index', compact(
            'draftRequests', 'pendingRequests', 'approvedRequests', 'rejectedRequests', 'lessonSessions'
        ));
    }

    /**
     * Thêm mới đơn xin nghỉ (Lưu nháp hoặc Gửi luôn)
     */
    public function store(Request $request)
    {
        $request->validate([
            'lesson_session_id' => 'required|exists:lesson_sessions,id',
            'reason'            => 'required|string|max:1000',
            'attachment'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'action'            => 'required|in:save_draft,submit_now'
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        $status = ($request->action === 'submit_now') ? 'Chờ duyệt' : 'Nháp';

        $leaveRequest = LeaveRequest::create([
            'reason'            => $request->reason,
            'attachment'        => $attachmentPath,
            'status'            => $status,
            'user_id'           => Auth::id(),
            'lesson_session_id' => $request->lesson_session_id,
            'submitted_at'      => ($status === 'Chờ duyệt') ? now() : null,
        ]);

        if ($request->action === 'submit_now') {
            app(NotificationService::class)->notifyLeaveRequestSubmitted($leaveRequest);
        }

        return redirect()->back()->with('success', $status === 'Chờ duyệt' ? 'Gửi đơn xin nghỉ thành công!' : 'Lưu nháp thành công!');
    }

    /**
     * Cập nhật thông tin đơn (Dành cho đơn Nháp)
     */
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($leaveRequest->status, ['Nháp', 'draft'])) {
            return redirect()->back()->with('error', 'Chỉ có thể chỉnh sửa đơn ở trạng thái Nháp.');
        }

        $request->validate([
            'lesson_session_id' => 'required|exists:lesson_sessions,id',
            'reason'            => 'required|string|max:1000',
            'attachment'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            if ($leaveRequest->attachment) {
                Storage::disk('public')->delete($leaveRequest->attachment);
            }
            $leaveRequest->attachment = $request->file('attachment')->store('leave_attachments', 'public');
        }

        $leaveRequest->update([
            'lesson_session_id' => $request->lesson_session_id,
            'reason'            => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Cập nhật đơn nháp thành công!');
    }

    /**
     * Gửi đơn từ trạng thái Nháp sang Chờ duyệt
     */
    public function submit($id)
    {
        $leaveRequest = LeaveRequest::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($leaveRequest->status, ['Nháp', 'draft'])) {
            return redirect()->back()->with('error', 'Đơn này đã được gửi hoặc xử lý trước đó.');
        }

        $leaveRequest->update([
            'status' => 'Chờ duyệt',
            'submitted_at' => now()
        ]);

        app(NotificationService::class)->notifyLeaveRequestSubmitted($leaveRequest);

        return redirect()->back()->with('success', 'Gửi đơn xin nghỉ thành công!');
    }

    /**
     * Thu hồi đơn từ Chờ duyệt quay về Nháp
     */
    public function withdraw($id)
    {
        $leaveRequest = LeaveRequest::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($leaveRequest->status, ['Chờ duyệt', 'pending'])) {
            return redirect()->back()->with('error', 'Không thể thu hồi đơn đã được xét duyệt.');
        }

        $leaveRequest->update([
            'status' => 'Nháp'
        ]);

        return redirect()->back()->with('success', 'Thu hồi đơn về trạng thái Nháp thành công!');
    }

    /**
     * Xóa đơn (Chỉ áp dụng với đơn Nháp hoặc đơn đang Chờ duyệt)
     */
    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::where('user_id', Auth::id())->findOrFail($id);

        if (in_array($leaveRequest->status, ['Đã duyệt', 'approved', 'Từ chối', 'rejected'])) {
            return redirect()->back()->with('error', 'Không thể xóa đơn đã xét duyệt.');
        }

        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return redirect()->back()->with('success', 'Xóa đơn thành công!');
    }
}
