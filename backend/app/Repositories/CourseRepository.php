<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function search(string $query)
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->get();
    }

    public function filter(array $filters)
    {
        $query = $this->model->query();

        if (isset($filters['tingkat_kesulitan'])) {
            $query->where('tingkat_kesulitan', $filters['tingkat_kesulitan']);
        }

        if (isset($filters['jenjang_pendidikan'])) {
            $query->where('jenjang_pendidikan', $filters['jenjang_pendidikan']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function getPublished()
    {
        return $this->model->where('status', 'published')->get();
    }

    public function getByMentor(int $mentorId)
    {
        return $this->model->where('mentor_id', $mentorId)->get();
    }
}

