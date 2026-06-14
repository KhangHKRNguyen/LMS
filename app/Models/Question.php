<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = [
        'question_number', 
        'question_type', 
        'question_text', 
        'points', 
        'max_recording_time', 
        'assignment_id', 
        'skill_id'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    // Các phương án lựa chọn (Cho dạng câu hỏi Trắc nghiệm)
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }

    // Các từ khóa đáp án (Cho dạng câu hỏi Điền từ)
    public function keywords(): HasMany
    {
        return $this->hasMany(QuestionKeyword::class, 'question_id')->orderBy('blank_order', 'asc');
    }
}