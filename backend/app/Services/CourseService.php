<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Repositories\CourseRepository;
use Illuminate\Support\Facades\DB;

class CourseService
{
    protected CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function getCourseRepository(): CourseRepository
    {
        return $this->courseRepository;
    }

    /**
     * Create new course
     */
    public function createCourse(array $data, int $mentorId): Course
    {
        $data['mentor_id'] = $mentorId;
        return $this->courseRepository->create($data);
    }

    /**
     * Update course
     */
    public function updateCourse(int $courseId, array $data, int $mentorId): Course
    {
        $course = $this->courseRepository->find($courseId);
        
        // Check ownership
        if ($course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized');
        }

        return $this->courseRepository->update($courseId, $data);
    }

    /**
     * Delete course
     */
    public function deleteCourse(int $courseId, int $mentorId): bool
    {
        $course = $this->courseRepository->find($courseId);
        
        // Check ownership
        if ($course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized');
        }

        return $this->courseRepository->delete($courseId);
    }

    /**
     * Search courses
     */
    public function searchCourses(string $query)
    {
        if (empty($query)) {
            return $this->courseRepository->getPublished();
        }
        return $this->courseRepository->search($query);
    }

    /**
     * Filter courses
     */
    public function filterCourses(array $filters)
    {
        $filters['status'] = 'published'; // Only show published courses
        return $this->courseRepository->filter($filters);
    }

    /**
     * Enroll pelajar to course
     */
    public function enrollCourse(int $courseId, int $pelajarId): CourseEnrollment
    {
        // Check if already enrolled
        $existing = CourseEnrollment::where('course_id', $courseId)
            ->where('pelajar_id', $pelajarId)
            ->first();

        if ($existing) {
            throw new \Exception('Anda sudah terdaftar di kursus ini');
        }

        // Check if course is published
        $course = $this->courseRepository->find($courseId);
        if (!$course->isPublished()) {
            throw new \Exception('Kursus belum dipublikasikan');
        }

        return CourseEnrollment::create([
            'pelajar_id' => $pelajarId,
            'course_id' => $courseId,
            'progress_percentage' => 0,
        ]);
    }
}

