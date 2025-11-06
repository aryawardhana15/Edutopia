<?php

namespace App\Repositories;

use App\Models\Quiz;

class QuizRepository extends BaseRepository
{
    public function __construct(Quiz $model)
    {
        parent::__construct($model);
    }

    public function getByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)->get();
    }

    public function getPublishedByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->where('status', 'published')
            ->get();
    }
}
