<?php

namespace App\Repositories;

use App\Models\AssignmentSubmission;
use App\Models\QuizSubmission;

class SubmissionRepository
{
    public function getAssignmentSubmissionsByAssignment(int $assignmentId)
    {
        return AssignmentSubmission::where('assignment_id', $assignmentId)
            ->with('pelajar')
            ->get();
    }

    public function getAssignmentSubmissionByPelajar(int $assignmentId, int $pelajarId)
    {
        return AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('pelajar_id', $pelajarId)
            ->first();
    }

    public function getQuizSubmissionsByQuiz(int $quizId)
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->with('pelajar')
            ->get();
    }

    public function getQuizSubmissionByPelajar(int $quizId, int $pelajarId)
    {
        return QuizSubmission::where('quiz_id', $quizId)
            ->where('pelajar_id', $pelajarId)
            ->first();
    }

    public function getSubmissionsByPelajar(int $pelajarId)
    {
        return [
            'assignments' => AssignmentSubmission::where('pelajar_id', $pelajarId)->get(),
            'quizzes' => QuizSubmission::where('pelajar_id', $pelajarId)->get(),
        ];
    }
}

