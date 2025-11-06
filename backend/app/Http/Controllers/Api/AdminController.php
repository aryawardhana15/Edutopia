<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Get pending mentors
     */
    public function pendingMentors(Request $request)
    {
        try {
            $mentors = \App\Models\Mentor::where('verification_status', 'pending')
                ->with('user')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $mentors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify mentor
     */
    public function verifyMentor(Request $request, $id)
    {
        try {
            $mentor = \App\Models\Mentor::findOrFail($id);
            $mentor->update([
                'verification_status' => 'approved',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mentor berhasil diverifikasi',
                'data' => $mentor->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject mentor
     */
    public function rejectMentor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mentor = \App\Models\Mentor::findOrFail($id);
            $mentor->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mentor ditolak',
                'data' => $mentor->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users
     */
    public function users(Request $request)
    {
        try {
            $query = \App\Models\User::query();

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->with(['pelajar', 'mentor'])->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend user
     */
    public function suspendUser(Request $request, $id)
    {
        try {
            $user = \App\Models\User::findOrFail($id);
            $user->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dinonaktifkan',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get forum reports
     */
    public function reports(Request $request)
    {
        try {
            $reports = \App\Models\ForumReport::where('status', 'pending')
                ->with(['reporter', 'reportable'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete forum post
     */
    public function deleteForumPost(Request $request, $id)
    {
        try {
            $post = \App\Models\ForumPost::findOrFail($id);
            $post->update([
                'is_deleted' => true,
                'deleted_by' => $request->user()->id,
                'deleted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete forum reply
     */
    public function deleteForumReply(Request $request, $id)
    {
        try {
            $reply = \App\Models\ForumComment::findOrFail($id);
            $reply->update([
                'is_deleted' => true,
                'deleted_by' => $request->user()->id,
                'deleted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reply berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard stats
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'total_users' => \App\Models\User::count(),
                'total_pelajar' => \App\Models\User::where('role', 'pelajar')->count(),
                'total_mentors' => \App\Models\User::where('role', 'mentor')->count(),
                'total_courses' => \App\Models\Course::count(),
                'published_courses' => \App\Models\Course::where('status', 'published')->count(),
                'total_enrollments' => \App\Models\CourseEnrollment::count(),
                'pending_mentors' => \App\Models\Mentor::where('verification_status', 'pending')->count(),
                'pending_reports' => \App\Models\ForumReport::where('status', 'pending')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

