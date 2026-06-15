<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submissions';

    protected $fillable = [
        'submission_time',
        'total_grade',
        'listening_grade',
        'reading_grade',
        'writing_grade',
        'speaking_grade',
        'teacher_comment',
        'attempt_number',
        'status',
        'user_id',
        'assignment_distribution_id'
    ];

    protected function casts(): array
    {
        return [
            'submission_time' => 'datetime',
            'total_grade'     => 'float',
            'listening_grade' => 'float',
            'reading_grade'   => 'float',
            'writing_grade'   => 'float',
            'speaking_grade'  => 'float',
        ];
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(AssignmentDistribution::class, 'assignment_distribution_id');
    }

    public function assignmentDistribution(): BelongsTo
    {
        return $this->belongsTo(AssignmentDistribution::class, 'assignment_distribution_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}