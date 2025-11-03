<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'xp_reward',
        'level_reward',
        'is_active',
    ];

    protected $casts = [
        'xp_reward' => 'integer',
        'level_reward' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }

    /**
     * Get active missions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

