<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AnswerFillBlank extends Model {
    protected $table = 'answers_fill_blank';
    protected $fillable = ['submission_id', 'question_id'];
    public function details() { return $this->hasMany(AnswerFillBlankDetail::class, 'answer_fill_blank_id'); }
}