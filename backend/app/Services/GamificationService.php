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
}

