<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuestionKeyword extends Model {
    protected $table = 'question_keywords';
    protected $fillable = ['blank_order', 'correct_keyword', 'question_id'];
    
    public function question() { return $this->belongsTo(Question::class); }
}