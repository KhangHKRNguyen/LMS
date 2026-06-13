<?php

namespace App\Http\Controllers\TA;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    // Xem đơn xin nghỉ của riêng các lớp TA này phụ trách
    public function index()
    {
        $ta = Auth::user();
        $classIds = $ta->classes()->pluck('course_classes.id')->toArray();

        $requests = LeaveRequest::whereHas('lessonSession', function($q) use ($classIds) {
                $q->whereIn('course_class_id', $classIds);
            })
            ->with(['student', 'lessonSession.courseClass']) 
            ->latest()
            ->get();

        // Phân nhóm đơn theo trạng thái
        $pendingRequests  = $requests->where('status', 'pending');
        $approvedRequests = $requests->where('status', 'approved');
        $rejectedRequests = $requests->where('status', 'rejected');

        return view('ta.leave_requests.index', compact('pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    // TA bấm Duyệt hoặc Từ chối
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        // Cập nhật trạng thái dựa trên cấu trúc database cũ (Không động vào Model/Migration)
        $leaveRequest->update([
            'status'      => $request->status,
            'receiver_id' => Auth::id() 
        ]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn thành công!');
    }
}