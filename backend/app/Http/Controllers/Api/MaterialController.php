<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    protected MaterialService $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Get all materials for a course
     */
    public function index(Request $request, $courseId)
    {
        try {
            $materials = $this->materialService->getMaterialsByCourse($courseId);

            return response()->json([
                'success' => true,
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get material detail
     */
    public function show($id)
    {
        try {
            $material = $this->materialService->getMaterialRepository()->find($id);
            $material->load('course');

            return response()->json([
                'success' => true,
                'data' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Materi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create new material (Mentor only)
     */
    public function store(Request $request, $courseId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,video,document,link',
            'content_path' => 'nullable|string',
            'order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $material = $this->materialService->createMaterial(
                $request->all(),
                $courseId,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil dibuat',
                'data' => $material
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized. You are not the owner of this course.' ? 403 : 500);
        }
    }

    /**
     * Update material (Mentor only)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:text,video,document,link',
            'content_path' => 'nullable|string',
            'order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $material = $this->materialService->updateMaterial(
                $id,
                $request->all(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil diperbarui',
                'data' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized. You are not the owner of this course.' ? 403 : 500);
        }
    }

    /**
     * Delete material (Mentor only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->materialService->deleteMaterial($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized. You are not the owner of this course.' ? 403 : 500);
        }
    }

    /**
     * Mark material as completed (Pelajar only)
     */
    public function complete(Request $request, $id)
    {
        try {
            $result = $this->materialService->completeMaterial($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Materi berhasil ditandai selesai',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
