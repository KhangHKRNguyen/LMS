<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AnswerWriting extends Model {
    protected $table = 'answers_writing';
    protected $fillable = ['essay_content', 'word_count', 'teacher_score', 'submission_id', 'question_id'];
}