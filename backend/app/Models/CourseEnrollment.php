<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelajar_id',
        'course_id',
        'progress_percentage',
        'enrolled_at',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'enrolled_at' => 'datetime',
    ];

    // Relationships
    public function pelajar()
    {
        return $this->belongsTo(User::class, 'pelajar_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Update progress percentage
     */
    public function updateProgress(int $percentage): void
    {
        $this->progress_percentage = min(100, max(0, $percentage));
        $this->save();
    }
}

