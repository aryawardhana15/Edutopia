<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'options',
        'correct_answer',
        'score',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'score' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Check if answer is correct
     */
    public function isCorrectAnswer(string $answer): bool
    {
        return $this->correct_answer === $answer;
    }
}

