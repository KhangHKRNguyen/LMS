<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 
        'correct_option', 'type', 'assignment_id', 'question_group',
        'passage', 'audio_path'
    ];

    /**
     * Quan hệ N-1: Question thuộc 1 Assignment (Bài tập)
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Quan hệ 1-N: Question có nhiều StudentAnswers (Đáp án học viên)
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'question_id');
    }
}