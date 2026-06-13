<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\CourseClass;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Xem danh sách đơn xin nghỉ của học viên (Kèm thông tin buổi học và lớp)
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->with(['lessonSession.courseClass']) // Eager load qua Buổi học rồi tới Lớp học
            ->orderBy('request_date', 'desc')
            ->paginate(10);

        return view('student.leave_requests.index', compact('leaveRequests'));
    }

    /**
     * Hiển thị giao diện tạo đơn xin nghỉ
     */
    public function create()
    {
        // Lấy danh sách các lớp mà học viên này đang theo học
        $classes = Auth::user()->classes; 

        return view('student.leave_requests.create', compact('classes'));
    }

    /**
     * Lưu đơn xin nghỉ bám theo kiến trúc LessonSession
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_class_id' => 'required|exists:course_classes,id',
            'request_date'    => 'required|date|after_or_equal:today',
            'reason'          => 'required|string|max:1000',
            'file'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' // Đính kèm minh chứng nếu có
        ], [
            'request_date.after_or_equal' => 'Ngày xin nghỉ phải từ ngày hôm nay trở đi.',
        ]);

        // 1. Đồng bộ/Tạo trước Buổi học (LessonSession) tương ứng với ngày học viên muốn nghỉ
        $lessonSession = LessonSession::firstOrCreate([
            'course_class_id' => $request->course_class_id,
            'session_date'    => $request->request_date,
        ]);

        // 2. Xử lý file đính kèm (nếu có bổ sung theo Figma của bạn)
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('leave_proofs', 'public');
        }

        // 3. Tạo đơn xin nghỉ gắn chặt vào lesson_session_id
        LeaveRequest::create([
            'user_id'           => Auth::id(),
            'lesson_session_id' => $lessonSession->id,
            'request_date'      => $request->request_date,
            'reason'            => $request->reason,
            'file_path'         => $filePath,
            'status'            => 'pending', // Mặc định chờ duyệt
            'receiver_id'       => null,       // Sẽ cập nhật khi có TA hoặc Admin bấm duyệt
        ]);

        return redirect()->route('student.leave_requests.index')
            ->with('success', 'Đơn xin nghỉ học của bạn đã được gửi tới Trợ lý lớp học.');
    }
}