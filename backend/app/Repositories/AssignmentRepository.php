<?php

namespace App\Repositories;

use App\Models\Assignment;

class AssignmentRepository extends BaseRepository
{
    public function __construct(Assignment $model)
    {
        parent::__construct($model);
    }

    public function getByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->orderBy('deadline')
            ->get();
    }

    public function getPublishedByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->where('status', 'published')
            ->orderBy('deadline')
            ->get();
    }

    public function getByMentor(int $mentorId)
    {
        return $this->model->where('mentor_id', $mentorId)->get();
    }
}

