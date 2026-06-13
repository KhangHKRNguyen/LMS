<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submissions';

    protected $fillable = [
        'submission_content',
        'file_path',
        'grade',
        'teacher_comment',
        'status',
        'assignment_id',
        'user_id',
        'listening_score',
        'reading_score',
        'writing_score',
        'speaking_score',
    ];

    /**
     * Cấu hình casts theo format mới của Laravel 11
     */
    protected function casts(): array
    {
        return [
            'grade' => 'float',
            'listening_score' => 'float',
            'reading_score' => 'float',
            'writing_score' => 'float',
            'speaking_score' => 'float',
        ];
    }

    /**
     * Quan hệ N-1: Submission thuộc 1 Assignment
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Quan hệ N-1: Submission thuộc 1 User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ N-1: Submission thuộc 1 User (Alias tương thích ngược)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ 1-N: Submission có nhiều StudentAnswers
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'submission_id');
    }

    /**
     * Quan hệ 1-N: Submission có nhiều StudentAnswers (Alias tương thích ngược)
     */
    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'submission_id');
    }

    /**
     * Quan hệ 1-N: Một bài nộp có thể có nhiều phản hồi khiếu nại/trao đổi điểm số
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'submission_id');
    }

    /**
     * Kiểm tra xem bài nộp đã được chấm điểm chưa
     */
    public function isGraded(): bool
    {
        if ($this->grade !== null) {
            return true;
        }

        return str_contains(mb_strtolower((string) $this->status), 'graded')
            || str_contains(mb_strtolower((string) $this->status), 'chấm')
            || str_contains(mb_strtolower((string) $this->status), 'cham');
    }
}