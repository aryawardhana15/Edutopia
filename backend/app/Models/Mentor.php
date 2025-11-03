<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    use HasFactory;

    protected $table = 'mentor';

    protected $fillable = [
        'user_id',
        'cv_path',
        'bidang_keahlian',
        'pengalaman',
        'verification_status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'mentor_id', 'user_id');
    }

    /**
     * Check if mentor is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'approved';
    }

    /**
     * Check if mentor is pending verification
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }
}

