<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Create assignment
     */
    public function createAssignment(array $data, int $courseId, int $mentorId): Assignment
    {
        $course = \App\Models\Course::findOrFail($courseId);
        if ($course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized');
        }

        $data['course_id'] = $courseId;
        $data['mentor_id'] = $mentorId;
        return Assignment::create($data);
    }

    /**
     * Submit assignment
     */
    public function submitAssignment(int $assignmentId, int $pelajarId, array $data, bool $allowResubmit = false): AssignmentSubmission
    {
        $assignment = Assignment::findOrFail($assignmentId);
        
        // Check deadline
        if (now() > $assignment->deadline) {
            throw new \Exception('Deadline sudah lewat');
        }

        // Check if already submitted
        $existing = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('pelajar_id', $pelajarId)
            ->first();

        if ($existing) {
            if (!$allowResubmit) {
                throw new \Exception('Anda sudah mengumpulkan tugas ini');
            }
            
            // Allow resubmit if not graded yet and before deadline
            if ($existing->status === 'graded') {
                throw new \Exception('Tugas sudah dinilai, tidak dapat diubah');
            }
            
            // Update existing submission
            $existing->update([
                'submission_content' => $data['submission_content'] ?? $existing->submission_content,
                'file_attachment' => $data['file_attachment'] ?? $existing->file_attachment,
                'submitted_at' => now(),
            ]);
            
            return $existing->fresh();
        }

        $data['assignment_id'] = $assignmentId;
        $data['pelajar_id'] = $pelajarId;
        $data['status'] = 'submitted';
        $data['submitted_at'] = now();

        return AssignmentSubmission::create($data);
    }

    /**
     * Grade assignment
     */
    public function gradeSubmission(int $submissionId, int $score, string $feedback = null): AssignmentSubmission
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);
        $submission->grade($score, $feedback);

        // Award XP for completing assignment
        try {
            $this->gamificationService->addXP($submission->pelajar_id, 20, 'Menyelesaikan tugas');
        } catch (\Exception $e) {
            // Ignore if user is not pelajar
        }

        return $submission->fresh();
    }
}
