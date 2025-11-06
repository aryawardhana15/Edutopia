<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pelajar;
use App\Models\Mission;
use App\Models\UserProgress;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Complete mission and award XP/Level
     */
    public function completeMission(int $userId, int $missionId, ?int $relatedId = null, string $progressType = 'mission'): array
    {
        return DB::transaction(function () use ($userId, $missionId, $relatedId, $progressType) {
            $mission = Mission::findOrFail($missionId);
            $user = User::findOrFail($userId);
            $pelajar = $user->pelajar;

            if (!$pelajar) {
                throw new \Exception('User is not a pelajar');
            }

            // Create progress record
            $progress = UserProgress::create([
                'user_id' => $userId,
                'mission_id' => $missionId,
                'progress_type' => $progressType,
                'related_id' => $relatedId,
                'xp_earned' => $mission->xp_reward,
                'level_points' => $mission->level_reward,
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // Award XP
            $levelResult = $pelajar->addXP($mission->xp_reward);

            // Check for badges
            $newBadges = $this->checkAndAwardBadges($user);

            return [
                'progress' => $progress,
                'level_up' => $levelResult['level_up'],
                'new_level' => $levelResult['new_level'],
                'current_xp' => $levelResult['current_xp'],
                'badges_earned' => $newBadges,
            ];
        });
    }

    /**
     * Check and award badges
     */
    public function checkAndAwardBadges(User $user): array
    {
        $newBadges = [];
        $badges = Badge::all();

        foreach ($badges as $badge) {
            // Skip if user already has this badge
            if ($user->badges->contains($badge->id)) {
                continue;
            }

            // Check if user meets condition
            if ($badge->checkCondition($user)) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    /**
     * Get user progress summary
     */
    public function getUserProgress(int $userId): array
    {
        $user = User::findOrFail($userId);
        $pelajar = $user->pelajar;

        if (!$pelajar) {
            throw new \Exception('User is not a pelajar');
        }

        return [
            'level' => $pelajar->current_level,
            'xp' => $pelajar->current_xp,
            'xp_needed' => $pelajar->current_level * 100 - $pelajar->current_xp,
            'badges' => $user->badges,
            'missions_completed' => $user->userProgress()->where('is_completed', true)->count(),
        ];
    }

    /**
     * Reset XP at end of week (can be called by scheduler)
     */
    public function resetWeeklyXP(): void
    {
        Pelajar::query()->update(['current_xp' => 0]);
    }

    /**
     * Add XP with reason
     */
    public function addXP(int $userId, int $amount, string $reason = ''): array
    {
        $user = User::findOrFail($userId);
        $pelajar = $user->pelajar;

        if (!$pelajar) {
            throw new \Exception('User is not a pelajar');
        }

        $levelResult = $pelajar->addXP($amount);

        // Log activity
        \App\Models\ActivityLog::log('xp_earned', $userId, 'User', $userId, [
            'amount' => $amount,
            'reason' => $reason,
            'level_up' => $levelResult['level_up'],
        ]);

        return $levelResult;
    }

    /**
     * Check level up
     */
    public function checkLevelUp(int $userId): bool
    {
        $user = User::findOrFail($userId);
        $pelajar = $user->pelajar;

        if (!$pelajar) {
            return false;
        }

        $xpNeeded = $pelajar->current_level * 100;
        return $pelajar->current_xp >= $xpNeeded;
    }

    /**
     * Award badge to user
     */
    public function awardBadge(int $userId, int $badgeId): bool
    {
        $user = User::findOrFail($userId);
        $badge = Badge::findOrFail($badgeId);

        // Check if already has badge
        if ($user->badges->contains($badgeId)) {
            return false;
        }

        $user->badges()->attach($badgeId, ['earned_at' => now()]);

        // Log activity
        \App\Models\ActivityLog::log('badge_earned', $userId, 'Badge', $badgeId);

        return true;
    }

    /**
     * Check mission progress
     */
    public function checkMissionProgress(int $userId, int $missionId): array
    {
        $mission = Mission::findOrFail($missionId);
        $user = User::findOrFail($userId);

        $progress = UserProgress::where('user_id', $userId)
            ->where('mission_id', $missionId)
            ->where('is_completed', true)
            ->first();

        return [
            'mission' => $mission,
            'completed' => $progress !== null,
            'completed_at' => $progress?->completed_at,
        ];
    }

    /**
     * Get user stats
     */
    public function getUserStats(int $userId): array
    {
        $user = User::findOrFail($userId);
        $pelajar = $user->pelajar;

        if (!$pelajar) {
            throw new \Exception('User is not a pelajar');
        }

        $totalXP = UserProgress::where('user_id', $userId)
            ->sum('xp_earned');

        $completedMissions = UserProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->whereNotNull('mission_id')
            ->count();

        $totalMissions = Mission::where('is_active', true)->count();

        return [
            'level' => $pelajar->current_level,
            'current_xp' => $pelajar->current_xp,
            'total_xp' => $totalXP,
            'xp_needed' => ($pelajar->current_level * 100) - $pelajar->current_xp,
            'badges_count' => $user->badges->count(),
            'badges' => $user->badges,
            'missions_completed' => $completedMissions,
            'missions_total' => $totalMissions,
            'progress_percentage' => $totalMissions > 0 ? ($completedMissions / $totalMissions) * 100 : 0,
        ];
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $pelajars = Pelajar::with('user')
            ->orderBy('current_level', 'desc')
            ->orderBy('current_xp', 'desc')
            ->limit($limit)
            ->get();

        $leaderboard = [];
        $rank = 1;

        foreach ($pelajars as $pelajar) {
            $totalXP = UserProgress::where('user_id', $pelajar->user_id)
                ->sum('xp_earned');

            $leaderboard[] = [
                'rank' => $rank++,
                'user' => $pelajar->user,
                'level' => $pelajar->current_level,
                'current_xp' => $pelajar->current_xp,
                'total_xp' => $totalXP,
                'badges_count' => $pelajar->user->badges->count(),
            ];
        }

        return $leaderboard;
    }

    /**
     * Get active missions for user
     */
    public function getActiveMissions(int $userId): array
    {
        $missions = Mission::active()->get();
        $userMissions = [];

        foreach ($missions as $mission) {
            $progress = $this->checkMissionProgress($userId, $mission->id);
            $userMissions[] = [
                'mission' => $mission,
                'completed' => $progress['completed'],
                'completed_at' => $progress['completed_at'],
            ];
        }

        return $userMissions;
    }
}

