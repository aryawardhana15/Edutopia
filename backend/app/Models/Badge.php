<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'condition_type',
        'condition_value',
    ];

    protected $casts = [
        'condition_value' => 'integer',
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')->withTimestamps();
    }

    /**
     * Check if user meets badge condition
     */
    public function checkCondition(User $user): bool
    {
        $pelajar = $user->pelajar;
        if (!$pelajar) {
            return false;
        }

        return match ($this->condition_type) {
            'level_reach' => $pelajar->current_level >= $this->condition_value,
            'xp_total' => $pelajar->current_xp >= $this->condition_value,
            'course_complete' => $user->enrollments()->where('progress_percentage', 100)->count() >= $this->condition_value,
            'mission_complete' => $user->userProgress()->where('is_completed', true)->count() >= $this->condition_value,
            default => false,
        };
    }
}

