<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'mentor_id',
        'title',
        'instruction',
        'duration',
        'max_score',
        'weight',
        'deadline',
        'status',
        'published_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'max_score' => 'integer',
        'weight' => 'integer',
        'deadline' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    /**
     * Check if quiz is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}

