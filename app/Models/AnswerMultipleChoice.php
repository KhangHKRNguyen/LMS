<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AnswerMultipleChoice extends Model {
    protected $table = 'answers_multiple_choice';
    protected $fillable = ['is_auto_correct', 'submission_id', 'question_id', 'question_option_id'];
    public function question() { return $this->belongsTo(Question::class); }
    public function option() { return $this->belongsTo(QuestionOption::class, 'question_option_id'); }
}