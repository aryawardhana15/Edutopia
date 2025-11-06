<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'likes',
        'is_deleted',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'likes' => 'integer',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(ForumPost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    public function reports()
    {
        return $this->morphMany(ForumReport::class, 'reportable');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get active (not deleted) comments
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}

