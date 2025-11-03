<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'mentor_id',
        'title',
        'instruction',
        'file_attachment',
        'deadline',
        'weight',
        'max_score',
        'status',
        'published_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'weight' => 'integer',
        'max_score' => 'integer',
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

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Check if assignment is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}

