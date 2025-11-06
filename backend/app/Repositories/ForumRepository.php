<?php

namespace App\Repositories;

use App\Models\ForumPost;

class ForumRepository extends BaseRepository
{
    public function __construct(ForumPost $model)
    {
        parent::__construct($model);
    }

    public function getByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->where('is_deleted', false)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function search(string $query)
    {
        return $this->model->where('is_deleted', false)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->get();
    }
}
