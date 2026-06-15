<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentDistribution extends Model
{
    use HasFactory;

    protected $table = 'assignment_distributions';

    protected $fillable = [
        'duration_minutes',
        'open_time',
        'close_time',
        'max_attempts',
        'status',
        'lesson_session_id',
        'assignment_id',
        'user_id',
    ];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'duration_minutes' => 'integer',
        'max_attempts' => 'integer',
    ];

    /**
     * Quan hệ ngược về Buổi học (LessonSession)
     */
    public function lessonSession(): BelongsTo
    {
        return $this->belongsTo(LessonSession::class, 'lesson_session_id');
    }

    /**
     * Quan hệ ngược về kho Bài tập gốc (Assignment)
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Quan hệ về Giáo viên thực hiện giao bài
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assignment_distribution_id');
    }
}