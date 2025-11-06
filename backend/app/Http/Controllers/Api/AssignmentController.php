<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    protected AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Get assignments by course
     */
    public function index(Request $request, $courseId)
    {
        try {
            $assignments = \App\Models\Assignment::where('course_id', $courseId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignment detail
     */
    public function show($id)
    {
        try {
            $assignment = \App\Models\Assignment::with(['course', 'mentor'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create assignment (Mentor only)
     */
    public function store(Request $request, $courseId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'instruction' => 'required|string',
            'file_attachment' => 'nullable|string',
            'deadline' => 'required|date',
            'weight' => 'nullable|integer|min:0|max:100',
            'max_score' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,scheduled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assignment = $this->assignmentService->createAssignment(
                $request->all(),
                $courseId,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Assignment berhasil dibuat',
                'data' => $assignment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Submit assignment (Pelajar only)
     */
    public function submit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'submission_content' => 'nullable|string',
            'file_attachment' => 'nullable|string',
            'resubmit' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $allowResubmit = $request->get('resubmit', false);
            $submission = $this->assignmentService->submitAssignment(
                $id,
                $request->user()->id,
                $request->all(),
                $allowResubmit
            );

            return response()->json([
                'success' => true,
                'message' => $allowResubmit ? 'Tugas berhasil diperbarui' : 'Tugas berhasil dikumpulkan',
                'data' => $submission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get submissions (Mentor only)
     */
    public function submissions(Request $request, $id)
    {
        try {
            $assignment = \App\Models\Assignment::findOrFail($id);
            
            if ($assignment->mentor_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $submissions = AssignmentSubmission::where('assignment_id', $id)
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

    /**
     * Grade submission (Mentor only)
     */
    public function grade(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0',
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $submission = $this->assignmentService->gradeSubmission(
                $id,
                $request->score,
                $request->feedback
            );

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diberikan',
                'data' => $submission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
