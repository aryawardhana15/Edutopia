<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    protected QuizService $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * Get quizzes by course
     */
    public function index(Request $request, int $courseId)
    {
        try {
            $publishedOnly = !$request->user()->isMentor();
            $quizzes = $this->quizService->getQuizzesByCourse($courseId, $publishedOnly);

            return response()->json([
                'success' => true,
                'data' => $quizzes->load(['course', 'questions'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quiz detail
     */
    public function show(Request $request, int $id)
    {
        try {
            $quiz = \App\Models\Quiz::with(['course', 'questions'])->findOrFail($id);
            
            // If pelajar, add submission status and hide correct answers
            if ($request->user()->isPelajar()) {
                $submission = \App\Models\QuizSubmission::where('quiz_id', $id)
                    ->where('pelajar_id', $request->user()->id)
                    ->first();
                $quiz->submission = $submission;
                
                // Hide correct answers if not submitted
                if (!$submission || $submission->status !== 'graded') {
                    foreach ($quiz->questions as $question) {
                        unset($question->correct_answer);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create quiz (Mentor only)
     */
    public function store(Request $request, int $courseId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'max_score' => 'required|integer|min:1',
            'weight' => 'nullable|integer|min:0|max:100',
            'deadline' => 'nullable|date',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,essay',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
            'questions.*.correct_answer' => 'required|string',
            'questions.*.score' => 'required|integer|min:1',
            'questions.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $quizData = $request->except('questions');
            $quiz = $this->quizService->createQuiz($quizData, $courseId, $request->user()->id);

            // Create questions
            foreach ($request->questions as $questionData) {
                \App\Models\QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionData['question'],
                    'type' => $questionData['type'],
                    'options' => $questionData['options'] ?? null,
                    'correct_answer' => $questionData['correct_answer'],
                    'score' => $questionData['score'],
                    'order' => $questionData['order'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dibuat',
                'data' => $quiz->load('questions')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Update quiz (Mentor only)
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'instruction' => 'nullable|string',
            'duration' => 'sometimes|required|integer|min:1',
            'max_score' => 'sometimes|required|integer|min:1',
            'weight' => 'nullable|integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $quiz = $this->quizService->updateQuiz($id, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil diperbarui',
                'data' => $quiz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Delete quiz (Mentor only)
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $this->quizService->deleteQuiz($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Submit quiz (Pelajar only)
     */
    public function submit(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if (!$request->user()->isPelajar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pelajar yang dapat submit quiz'
                ], 403);
            }

            $submission = $this->quizService->submitQuiz(
                $id,
                $request->answers,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dikumpulkan',
                'data' => $submission->load('quiz.questions')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get submissions (Mentor only)
     */
    public function submissions(Request $request, int $id)
    {
        try {
            if (!$request->user()->isMentor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $quiz = \App\Models\Quiz::findOrFail($id);
            if ($quiz->mentor_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $submissions = \App\Models\QuizSubmission::where('quiz_id', $id)
                ->with('pelajar')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

