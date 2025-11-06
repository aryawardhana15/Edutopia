<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Get user stats
     */
    public function stats(Request $request)
    {
        try {
            $stats = $this->gamificationService->getUserStats($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get leaderboard
     */
    public function leaderboard(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $leaderboard = $this->gamificationService->getLeaderboard($limit);

            return response()->json([
                'success' => true,
                'data' => $leaderboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user missions
     */
    public function missions(Request $request)
    {
        try {
            $missions = $this->gamificationService->getActiveMissions($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $missions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all badges
     */
    public function badges(Request $request)
    {
        try {
            $user = $request->user();
            $allBadges = \App\Models\Badge::all();
            $userBadges = $user->badges->pluck('id')->toArray();

            $badges = $allBadges->map(function ($badge) use ($userBadges) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon,
                    'unlocked' => in_array($badge->id, $userBadges),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $badges
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
