<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\UserProgress;
use App\Repositories\MaterialRepository;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

class MaterialService
{
    protected MaterialRepository $materialRepository;
    protected GamificationService $gamificationService;

    public function __construct(
        MaterialRepository $materialRepository,
        GamificationService $gamificationService
    ) {
        $this->materialRepository = $materialRepository;
        $this->gamificationService = $gamificationService;
    }

    /**
     * Create new material
     */
    public function createMaterial(array $data, int $courseId, int $mentorId): CourseMaterial
    {
        // Verify course ownership
        $course = Course::findOrFail($courseId);
        if ($course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized. You are not the owner of this course.');
        }

        // Set order if not provided
        if (!isset($data['order'])) {
            $data['order'] = $this->materialRepository->getNextOrder($courseId);
        }

        $data['course_id'] = $courseId;
        return $this->materialRepository->create($data);
    }

    /**
     * Update material
     */
    public function updateMaterial(int $materialId, array $data, int $mentorId): CourseMaterial
    {
        $material = $this->materialRepository->find($materialId);
        
        // Verify course ownership
        if ($material->course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized. You are not the owner of this course.');
        }

        return $this->materialRepository->update($materialId, $data);
    }

    /**
     * Delete material
     */
    public function deleteMaterial(int $materialId, int $mentorId): bool
    {
        $material = $this->materialRepository->find($materialId);
        
        // Verify course ownership
        if ($material->course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized. You are not the owner of this course.');
        }

        return $this->materialRepository->delete($materialId);
    }

    /**
     * Get materials by course
     */
    public function getMaterialsByCourse(int $courseId)
    {
        return $this->materialRepository->getByCourse($courseId);
    }

    public function getMaterialRepository(): MaterialRepository
    {
        return $this->materialRepository;
    }

    /**
     * Mark material as completed by pelajar
     */
    public function completeMaterial(int $materialId, int $pelajarId): array
    {
        return DB::transaction(function () use ($materialId, $pelajarId) {
            $material = $this->materialRepository->find($materialId);
            
            // Check if already completed
            $existingProgress = UserProgress::where('user_id', $pelajarId)
                ->where('progress_type', 'course')
                ->where('related_id', $materialId)
                ->where('is_completed', true)
                ->first();

            if ($existingProgress) {
                throw new \Exception('Materi ini sudah diselesaikan');
            }

            // Check enrollment
            $enrollment = \App\Models\CourseEnrollment::where('course_id', $material->course_id)
                ->where('pelajar_id', $pelajarId)
                ->first();

            if (!$enrollment) {
                throw new \Exception('Anda belum terdaftar di kursus ini');
            }

            // Create progress record
            $progress = UserProgress::create([
                'user_id' => $pelajarId,
                'progress_type' => 'course',
                'related_id' => $materialId,
                'xp_earned' => 10, // +10 XP for completing material
                'level_points' => 0,
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // Award XP
            $user = \App\Models\User::findOrFail($pelajarId);
            $pelajar = $user->pelajar;
            if ($pelajar) {
                $levelResult = $pelajar->addXP(10);
                
                // Check for badges
                $newBadges = $this->gamificationService->checkAndAwardBadges($user);
                
                // Update course progress
                $this->updateCourseProgress($material->course_id, $pelajarId);
                
                return [
                    'progress' => $progress,
                    'level_up' => $levelResult['level_up'],
                    'new_level' => $levelResult['new_level'],
                    'current_xp' => $levelResult['current_xp'],
                    'badges_earned' => $newBadges,
                ];
            }

            return ['progress' => $progress];
        });
    }

    /**
     * Update course progress percentage
     */
    protected function updateCourseProgress(int $courseId, int $pelajarId): void
    {
        $course = Course::findOrFail($courseId);
        $totalMaterials = $course->materials()->count();
        
        if ($totalMaterials === 0) {
            return;
        }

        $completedMaterials = UserProgress::where('user_id', $pelajarId)
            ->where('progress_type', 'course')
            ->whereIn('related_id', $course->materials()->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $progressPercentage = (int) (($completedMaterials / $totalMaterials) * 100);

        $enrollment = \App\Models\CourseEnrollment::where('course_id', $courseId)
            ->where('pelajar_id', $pelajarId)
            ->first();

        if ($enrollment) {
            $enrollment->updateProgress($progressPercentage);
            
            // If course completed (100%), award XP
            if ($progressPercentage === 100) {
                $user = \App\Models\User::findOrFail($pelajarId);
                $pelajar = $user->pelajar;
                if ($pelajar) {
                    $pelajar->addXP(50); // +50 XP for completing course
                    $this->gamificationService->checkAndAwardBadges($user);
                }
            }
        }
    }
}
