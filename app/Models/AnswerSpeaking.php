<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AnswerSpeaking extends Model {
    protected $table = 'answers_speaking';
    protected $fillable = ['audio_file_path', 'duration_seconds', 'teacher_score', 'submission_id', 'question_id'];
}