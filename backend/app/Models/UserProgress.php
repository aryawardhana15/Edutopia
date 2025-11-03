<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mission_id',
        'progress_type',
        'related_id',
        'xp_earned',
        'level_points',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'xp_earned' => 'integer',
        'level_points' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}

