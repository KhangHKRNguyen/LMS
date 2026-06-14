<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory;

    // Khai báo tên bảng chính xác trong database
    protected $table = 'skills';

    // Các trường được phép gán dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        'name',
    ];

    /**
     * Mối quan hệ 1-N: Một kỹ năng có thể áp dụng cho nhiều Câu hỏi (Questions)
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'skill_id');
    }
}