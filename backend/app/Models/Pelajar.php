<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelajar extends Model
{
    use HasFactory;

    protected $table = 'pelajar';

    protected $fillable = [
        'user_id',
        'jenjang_pendidikan',
        'peminatan',
        'current_level',
        'current_xp',
    ];

    protected $casts = [
        'current_level' => 'integer',
        'current_xp' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'pelajar_id', 'user_id');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'user_id', 'user_id');
    }

    /**
     * Add XP to pelajar and check for level up
     */
    public function addXP(int $xp): array
    {
        $this->current_xp += $xp;
        $levelUp = false;
        $newLevel = $this->current_level;
        
        // Level up calculation (100 XP per level)
        $xpNeeded = $this->current_level * 100;
        while ($this->current_xp >= $xpNeeded) {
            $this->current_xp -= $xpNeeded;
            $this->current_level++;
            $newLevel = $this->current_level;
            $levelUp = true;
            $xpNeeded = $this->current_level * 100;
        }
        
        $this->save();
        
        return [
            'level_up' => $levelUp,
            'new_level' => $newLevel,
            'current_xp' => $this->current_xp,
        ];
    }
}

