<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'pelajar_id',
        'submission_content',
        'file_attachment',
        'score',
        'feedback',
        'status',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function pelajar()
    {
        return $this->belongsTo(User::class, 'pelajar_id');
    }

    /**
     * Grade the submission
     */
    public function grade(int $score, string $feedback = null): void
    {
        $this->score = $score;
        $this->feedback = $feedback;
        $this->status = 'graded';
        $this->graded_at = now();
        $this->save();
    }
}

