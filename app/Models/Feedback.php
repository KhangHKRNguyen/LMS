<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Submission;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'content',
        'user_id',
        'submission_id'
    ];

    // Mối quan hệ với người gửi tin nhắn (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mối quan hệ ngược lại với Bài nộp (Submission)
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}