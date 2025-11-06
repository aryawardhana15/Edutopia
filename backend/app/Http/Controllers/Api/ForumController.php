<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumController extends Controller
{
    protected ForumService $forumService;

    public function __construct(ForumService $forumService)
    {
        $this->forumService = $forumService;
    }

    /**
     * Get posts by course
     */
    public function index(Request $request, $courseId)
    {
        try {
            $posts = $this->forumService->getPostsByCourse($courseId);
            $posts->load(['user', 'comments.user']);

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get post detail
     */
    public function show($id)
    {
        try {
            $post = \App\Models\ForumPost::with(['user', 'comments.user', 'course'])->findOrFail($id);
            $post->incrementViews();

            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Create post
     */
    public function store(Request $request, $courseId = null)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $post = $this->forumService->createPost(
                $request->all(),
                $request->user()->id,
                $courseId
            );

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil dibuat',
                'data' => $post->load('user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create reply
     */
    public function reply(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:forum_comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reply = $this->forumService->createReply(
                $postId,
                $request->content,
                $request->user()->id,
                $request->parent_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Reply berhasil dibuat',
                'data' => $reply->load('user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Like post
     */
    public function like(Request $request, $id)
    {
        try {
            $post = $this->forumService->likePost($id);

            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pin post (Mentor/Admin only)
     */
    public function pin(Request $request, $id)
    {
        try {
            $post = $this->forumService->pinPost($id, true);

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil di-pin',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock post (Mentor/Admin only)
     */
    public function lock(Request $request, $id)
    {
        try {
            $post = $this->forumService->lockPost($id, true);

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil dikunci',
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report post
     */
    public function report(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = $this->forumService->report(
                $id,
                'post',
                $request->user()->id,
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim',
                'data' => $report
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Like reply
     */
    public function likeReply(Request $request, $id)
    {
        try {
            $reply = $this->forumService->likeReply($id);

            return response()->json([
                'success' => true,
                'data' => $reply
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        try {
            $posts = $this->forumService->searchPosts($query);
            $posts->load('user');

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

