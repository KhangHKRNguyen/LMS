<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AnswerFillBlankDetail extends Model {
    protected $table = 'answer_fill_blank_details';
    protected $fillable = ['blank_order', 'student_input', 'is_correct', 'answer_fill_blank_id'];
}