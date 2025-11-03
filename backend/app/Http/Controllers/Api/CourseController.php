<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Get all courses (published only)
     */
    public function index(Request $request)
    {
        $courses = $this->courseService->searchCourses($request->get('search', ''));

        return response()->json([
            'success' => true,
            'data' => $courses->load(['mentor', 'materials'])
        ]);
    }

    /**
     * Filter courses
     */
    public function filter(Request $request)
    {
        $filters = $request->only(['tingkat_kesulitan', 'jenjang_pendidikan', 'category']);
        $courses = $this->courseService->filterCourses($filters);

        return response()->json([
            'success' => true,
            'data' => $courses->load(['mentor', 'materials'])
        ]);
    }

    /**
     * Get course detail
     */
    public function show($id)
    {
        try {
            $course = $this->courseService->getCourseRepository()->find($id);
            $course->load(['mentor', 'materials', 'enrollments']);

            return response()->json([
                'success' => true,
                'data' => $course
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kursus tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create new course (Mentor only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'tingkat_kesulitan' => 'required|in:pemula,menengah,lanjutan',
            'jenjang_pendidikan' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $course = $this->courseService->createCourse($request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Kursus berhasil dibuat',
                'data' => $course
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update course (Mentor only)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'nullable|string',
            'tingkat_kesulitan' => 'sometimes|required|in:pemula,menengah,lanjutan',
            'jenjang_pendidikan' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|string',
            'status' => 'sometimes|required|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $course = $this->courseService->updateCourse($id, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Kursus berhasil diperbarui',
                'data' => $course
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized' ? 403 : 500);
        }
    }

    /**
     * Delete course (Mentor only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->courseService->deleteCourse($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Kursus berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized' ? 403 : 500);
        }
    }

    /**
     * Enroll to course (Pelajar only)
     */
    public function enroll(Request $request, $id)
    {
        try {
            $enrollment = $this->courseService->enrollCourse($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil gabung dengan kursus',
                'data' => $enrollment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

