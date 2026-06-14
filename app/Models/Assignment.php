<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = ['title', 'description', 'file_path', 'assignment_type_id', 'user_id'];

    public function assignmentType(): BelongsTo
    {
        return $this->belongsTo(AssignmentType::class, 'assignment_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Một đề thi chứa nhiều câu hỏi (Sắp xếp theo thứ tự câu số)
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'assignment_id')->orderBy('question_number', 'asc');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(AssignmentDistribution::class, 'assignment_id');
    }
}