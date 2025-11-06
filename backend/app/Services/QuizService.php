<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Course;
use App\Repositories\QuizRepository;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

class QuizService
{
    protected QuizRepository $quizRepository;
    protected GamificationService $gamificationService;

    public function __construct(
        QuizRepository $quizRepository,
        GamificationService $gamificationService
    ) {
        $this->quizRepository = $quizRepository;
        $this->gamificationService = $gamificationService;
    }

    /**
     * Create new quiz
     */
    public function createQuiz(array $data, int $courseId, int $mentorId): Quiz
    {
        $course = Course::findOrFail($courseId);
        if ($course->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized: You are not the owner of this course');
        }

        $data['course_id'] = $courseId;
        $data['mentor_id'] = $mentorId;
        return $this->quizRepository->create($data);
    }

    /**
     * Update quiz
     */
    public function updateQuiz(int $quizId, array $data, int $mentorId): Quiz
    {
        $quiz = $this->quizRepository->find($quizId);
        if ($quiz->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized: You are not the owner of this quiz');
        }

        return $this->quizRepository->update($quizId, $data);
    }

    /**
     * Delete quiz
     */
    public function deleteQuiz(int $quizId, int $mentorId): bool
    {
        $quiz = $this->quizRepository->find($quizId);
        if ($quiz->mentor_id !== $mentorId) {
            throw new \Exception('Unauthorized: You are not the owner of this quiz');
        }

        return $this->quizRepository->delete($quizId);
    }

    /**
     * Submit quiz
     */
    public function submitQuiz(int $quizId, array $answers, int $pelajarId): \App\Models\QuizSubmission
    {
        return DB::transaction(function () use ($quizId, $answers, $pelajarId) {
            $quiz = $this->quizRepository->find($quizId);

            // Check if already submitted
            $existing = \App\Models\QuizSubmission::where('quiz_id', $quizId)
                ->where('pelajar_id', $pelajarId)
                ->first();

            if ($existing && $existing->status === 'submitted') {
                throw new \Exception('Quiz sudah pernah dikerjakan');
            }

            // Create or update submission
            $submission = \App\Models\QuizSubmission::updateOrCreate(
                [
                    'quiz_id' => $quizId,
                    'pelajar_id' => $pelajarId,
                ],
                [
                    'answers' => $answers,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]
            );

            // Auto-grade quiz
            $submission->calculateScore();

            // Award XP
            $user = \App\Models\User::find($pelajarId);
            $user->pelajar->addXP(20);
            $this->gamificationService->checkAndAwardBadges($user);

            return $submission;
        });
    }

    /**
     * Get quizzes by course
     */
    public function getQuizzesByCourse(int $courseId, bool $publishedOnly = false)
    {
        if ($publishedOnly) {
            return $this->quizRepository->getPublishedByCourse($courseId);
        }
        return $this->quizRepository->getByCourse($courseId);
    }
}

