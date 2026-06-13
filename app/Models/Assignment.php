<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = [
        'title',
        'content',
        'type',
        'open_time',
        'due_time',
        'file_path',
        'course_class_id',
        'is_visible',
        'skill',
        'audio_path',
        'passage',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'open_time' => 'datetime',
            'due_time' => 'datetime',
            'is_visible' => 'boolean',
            'duration_minutes' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'assignment_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }

    /**
     * Kiểm tra bài tập có phải trắc nghiệm không (Đã sửa lỗi logic)
     */
    public function isquiz(): bool
    {
        $type = $this->normalizedType();

        // Chuỗi trả về từ normalizedType() đã loại bỏ dấu tiếng Việt và viết thường
        return str_contains($type, 'trac_nghiem')
            || (str_contains($type, 'tr') && str_contains($type, 'nghi'));
    }

    public function isEssay(): bool
    {
        $type = $this->normalizedType();

        return str_contains($type, 'tu_luan')
            || str_contains($type, 'luan');
    }

    public function typeLabel(): string
    {
        return $this->isquiz() ? 'Trắc nghiệm' : 'Tự luận';
    }

    private function normalizedType(): string
    {
        $type = Str::lower(Str::ascii(trim((string) $this->type)));
        return trim((string) preg_replace('/[^a-z0-9]+/', '_', $type), '_');
    }
}