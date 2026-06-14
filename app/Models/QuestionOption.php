<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model {
    protected $table = 'question_options';
    protected $fillable = ['option_letter', 'option_content', 'is_correct', 'question_id'];
    
    public function question() { return $this->belongsTo(Question::class); }
}