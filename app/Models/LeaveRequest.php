<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'reason', 
        'attachment', 
        'submitted_at', 
        'status', 
        'user_id', 
        'approver_id', 
        'lesson_session_id'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Đơn xin nghỉ này áp dụng riêng cho buổi học nào
    public function lessonSession()
    {
        return $this->belongsTo(LessonSession::class, 'lesson_session_id');
    }

    // Học viên viết đơn
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Người duyệt đơn (TA / Admin)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}