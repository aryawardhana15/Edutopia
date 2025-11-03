<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'pelajar_id',
        'answers',
        'score',
        'total_score',
        'started_at',
        'submitted_at',
        'time_spent',
        'status',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'integer',
        'total_score' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'time_spent' => 'integer',
    ];

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function pelajar()
    {
        return $this->belongsTo(User::class, 'pelajar_id');
    }

    /**
     * Calculate and set score automatically
     */
    public function calculateScore(): void
    {
        $quiz = $this->quiz;
        $questions = $quiz->questions;
        $answers = $this->answers;
        
        $score = 0;
        $totalScore = 0;
        
        foreach ($questions as $question) {
            $totalScore += $question->score;
            $userAnswer = $answers[$question->id] ?? null;
            
            if ($userAnswer && $question->isCorrectAnswer($userAnswer)) {
                $score += $question->score;
            }
        }
        
        $this->score = $score;
        $this->total_score = $totalScore;
        $this->status = 'graded';
        $this->save();
    }
}

