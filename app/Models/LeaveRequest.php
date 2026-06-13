<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'request_date', 'reason', 'user_id',
        // Các trường mới refactor ở Bước 3:
        'lesson_session_id', 'receiver_id', 'file_path', 'status'
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
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}