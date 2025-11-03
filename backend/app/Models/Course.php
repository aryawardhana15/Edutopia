<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'category',
        'tingkat_kesulitan',
        'jenjang_pendidikan',
        'harga',
        'thumbnail',
        'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    // Relationships
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Check if course is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Get enrolled students count
     */
    public function getEnrolledCountAttribute(): int
    {
        return $this->enrollments()->count();
    }
}

