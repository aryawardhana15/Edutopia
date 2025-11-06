<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\CourseEnrollment;
use App\Repositories\ForumRepository;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;

class ForumService
{
    protected ForumRepository $forumRepository;
    protected GamificationService $gamificationService;

    public function __construct(
        ForumRepository $forumRepository,
        GamificationService $gamificationService
    ) {
        $this->forumRepository = $forumRepository;
        $this->gamificationService = $gamificationService;
    }

    /**
     * Create forum post
     */
    public function createPost(array $data, int $userId, ?int $courseId = null): ForumPost
    {
        // If course_id provided, verify enrollment
        if ($courseId) {
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('pelajar_id', $userId)
                ->first();

            if (!$enrollment) {
                throw new \Exception('Anda belum terdaftar di kursus ini');
            }
        }

        $data['user_id'] = $userId;
        $data['course_id'] = $courseId;
        $post = $this->forumRepository->create($data);

        // Award XP for posting
        try {
            $this->gamificationService->addXP($userId, 5, 'Posting di forum');
        } catch (\Exception $e) {
            // Ignore if user is not pelajar
        }

        return $post;
    }

    /**
     * Create reply
     */
    public function createReply(int $postId, string $content, int $userId, ?int $parentId = null): ForumComment
    {
        $post = ForumPost::findOrFail($postId);

        // Check if post is locked
        if ($post->is_locked) {
            throw new \Exception('Thread ini sudah dikunci');
        }

        // If course post, verify enrollment
        if ($post->course_id) {
            $enrollment = CourseEnrollment::where('course_id', $post->course_id)
                ->where('pelajar_id', $userId)
                ->first();

            if (!$enrollment) {
                throw new \Exception('Anda belum terdaftar di kursus ini');
            }
        }

        $reply = ForumComment::create([
            'post_id' => $postId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $content,
        ]);

        // Award XP for replying
        try {
            $this->gamificationService->addXP($userId, 3, 'Membalas di forum');
        } catch (\Exception $e) {
            // Ignore if user is not pelajar
        }

        return $reply;
    }

    /**
     * Like post
     */
    public function likePost(int $postId): ForumPost
    {
        $post = ForumPost::findOrFail($postId);
        $post->increment('likes');
        return $post->fresh();
    }

    /**
     * Like reply
     */
    public function likeReply(int $replyId): ForumComment
    {
        $reply = ForumComment::findOrFail($replyId);
        $reply->increment('likes');
        return $reply->fresh();
    }

    /**
     * Pin post (Mentor/Admin only)
     */
    public function pinPost(int $postId, bool $pin = true): ForumPost
    {
        $post = ForumPost::findOrFail($postId);
        $post->update(['is_pinned' => $pin]);
        return $post->fresh();
    }

    /**
     * Lock post (Mentor/Admin only)
     */
    public function lockPost(int $postId, bool $lock = true): ForumPost
    {
        $post = ForumPost::findOrFail($postId);
        $post->update(['is_locked' => $lock]);
        return $post->fresh();
    }

    /**
     * Report post/reply
     */
    public function report(int $reportableId, string $reportableType, int $reporterId, string $reason): ForumReport
    {
        return ForumReport::create([
            'reporter_id' => $reporterId,
            'reportable_type' => $reportableType === 'post' ? ForumPost::class : ForumComment::class,
            'reportable_id' => $reportableId,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    /**
     * Get posts by course
     */
    public function getPostsByCourse(int $courseId)
    {
        return $this->forumRepository->getByCourse($courseId);
    }

    /**
     * Search posts
     */
    public function searchPosts(string $query)
    {
        return $this->forumRepository->search($query);
    }
}

