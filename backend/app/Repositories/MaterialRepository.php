<?php

namespace App\Repositories;

use App\Models\CourseMaterial;

class MaterialRepository extends BaseRepository
{
    public function __construct(CourseMaterial $model)
    {
        parent::__construct($model);
    }

    public function getByCourse(int $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->orderBy('order')
            ->get();
    }

    public function getNextOrder(int $courseId): int
    {
        $lastMaterial = $this->model->where('course_id', $courseId)
            ->orderBy('order', 'desc')
            ->first();
        
        return $lastMaterial ? $lastMaterial->order + 1 : 1;
    }
}
