<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\CourseClass; // Bổ sung Model để lấy thông tin lớp học
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Xem đơn xin nghỉ của một lớp học cụ thể do TA phụ trách
     */
    public function index($classId)
    {
        $ta = Auth::user();
        
        // 1. Lấy thông tin lớp hiện tại để truyền vào Layout Workspace (Sửa lỗi mất Sidebar)
        $class = CourseClass::findOrFail($classId);

        // Bảo mật: Kiểm tra xem TA này có thực sự quản lý lớp này không
        $allowedClassIds = $ta->classes()->pluck('course_classes.id')->toArray();
        if (!in_array($classId, $allowedClassIds)) {
            abort(403, 'Bạn không có quyền truy cập dữ liệu lớp học này.');
        }

        // 2. Lọc ĐƠN XIN NGHỈ: Chỉ lấy các đơn thuộc về đúng $classId đang xem
        $requests = LeaveRequest::whereHas('lessonSession', function($q) use ($classId) {
                $q->where('course_class_id', $classId);
            })
            ->with(['student', 'lessonSession.courseClass']) 
            ->latest()
            ->get();

        // 3. Phân nhóm trạng thái (Hỗ trợ song song cả tiếng Anh 'pending' trong DB và tiếng Việt)
        $pendingRequests  = $requests->filter(fn($r) => in_array($r->status, ['pending', 'Chờ duyệt']));
        $approvedRequests = $requests->filter(fn($r) => in_array($r->status, ['approved', 'Đã duyệt']));
        $rejectedRequests = $requests->filter(fn($r) => in_array($r->status, ['rejected', 'Từ chối']));

        // Truyền thêm biến $class sang View để file taclassroom.blade.php có dữ liệu chạy sidebar
        return view('ta.classes.leave_requests', compact('class', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Xử lý Duyệt hoặc Từ chối đơn xin nghỉ
     */
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        // Chấp nhận cả 2 định dạng trạng thái khi bấm update
        $request->validate([
            'status' => 'required|in:approved,rejected,Đã duyệt,Từ chối'
        ]);

        $leaveRequest->update([
            'status'      => $request->status,
            'approver_id' => Auth::id() 
        ]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn xin nghỉ thành công!');
    }
}