<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::post('/profile/cv', [ProfileController::class, 'uploadCV'])->middleware('mentor');

    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/filter', [CourseController::class, 'filter']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll'])->middleware('pelajar');

    // Material routes
    Route::get('/courses/{courseId}/materials', [MaterialController::class, 'index']);
    Route::get('/materials/{id}', [MaterialController::class, 'show']);
    Route::post('/materials/{id}/complete', [MaterialController::class, 'complete'])->middleware('pelajar');

    // Forum routes
    Route::get('/courses/{courseId}/forums', [ForumController::class, 'index']);
    Route::get('/forums/{id}', [ForumController::class, 'show']);
    Route::post('/courses/{courseId}/forums', [ForumController::class, 'store']);
    Route::post('/forums/{id}/replies', [ForumController::class, 'reply']);
    Route::post('/forums/{id}/like', [ForumController::class, 'like']);
    Route::post('/replies/{id}/like', [ForumController::class, 'likeReply']);
    Route::post('/forums/{id}/report', [ForumController::class, 'report']);
    Route::get('/forums/search', [ForumController::class, 'search']);

    // Chat routes
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/{userId}/messages', [ChatController::class, 'messages']);
    Route::post('/chats/{userId}/send', [ChatController::class, 'send']);
    Route::post('/chats/{userId}/read', [ChatController::class, 'markRead']);

    // Assignment routes
    Route::get('/courses/{courseId}/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/{id}', [AssignmentController::class, 'show']);
    Route::post('/assignments/{id}/submit', [AssignmentController::class, 'submit'])->middleware('pelajar');

    // Quiz routes
    Route::get('/courses/{courseId}/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submit'])->middleware('pelajar');

    // Gamification routes
    Route::get('/gamification/stats', [GamificationController::class, 'stats']);
    Route::get('/gamification/leaderboard', [GamificationController::class, 'leaderboard']);
    Route::get('/gamification/missions', [GamificationController::class, 'missions']);
    Route::get('/gamification/badges', [GamificationController::class, 'badges']);

    // File upload routes
    Route::post('/files/profile-photo', [FileController::class, 'uploadProfilePhoto']);
    Route::post('/files/cv', [FileController::class, 'uploadCV']);
    Route::post('/files/material', [FileController::class, 'uploadMaterialFile']);
    Route::post('/files/submission', [FileController::class, 'uploadSubmissionFile']);
    Route::get('/files/{filename}', [FileController::class, 'getFile']);

    // Mentor only routes
    Route::middleware('mentor')->group(function () {
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
        
        // Material CRUD
        Route::post('/courses/{courseId}/materials', [MaterialController::class, 'store']);
        Route::put('/materials/{id}', [MaterialController::class, 'update']);
        Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

        // Assignment CRUD
        Route::post('/courses/{courseId}/assignments', [AssignmentController::class, 'store']);
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions']);
        Route::post('/submissions/{id}/grade', [AssignmentController::class, 'grade']);

        // Quiz CRUD
        Route::post('/courses/{courseId}/quizzes', [QuizController::class, 'store']);
        Route::put('/quizzes/{id}', [QuizController::class, 'update']);
        Route::delete('/quizzes/{id}', [QuizController::class, 'destroy']);
        Route::get('/quizzes/{id}/submissions', [QuizController::class, 'submissions']);

        // Forum moderation
        Route::post('/forums/{id}/pin', [ForumController::class, 'pin']);
        Route::post('/forums/{id}/lock', [ForumController::class, 'lock']);
    });

    // Admin only routes
    Route::middleware('admin')->group(function () {
        // Mentor verification
        Route::get('/admin/mentors/pending', [AdminController::class, 'pendingMentors']);
        Route::post('/admin/mentors/{id}/verify', [AdminController::class, 'verifyMentor']);
        Route::post('/admin/mentors/{id}/reject', [AdminController::class, 'rejectMentor']);

        // User management
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::post('/admin/users/{id}/suspend', [AdminController::class, 'suspendUser']);

        // Forum moderation
        Route::get('/admin/reports', [AdminController::class, 'reports']);
        Route::delete('/admin/forums/{id}', [AdminController::class, 'deleteForumPost']);
        Route::delete('/admin/replies/{id}', [AdminController::class, 'deleteForumReply']);

        // Statistics
        Route::get('/admin/stats', [AdminController::class, 'stats']);
    });
});

