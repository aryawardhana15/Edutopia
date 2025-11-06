<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pelajar;
use App\Models\Mentor;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register new user
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'is_active' => $data['role'] === 'pelajar' ? true : false, // Pelajar langsung aktif
            ]);

            if ($data['role'] === 'pelajar') {
                Pelajar::create([
                    'user_id' => $user->id,
                    'jenjang_pendidikan' => $data['jenjang_pendidikan'] ?? null,
                    'peminatan' => $data['peminatan'] ?? null,
                ]);
            } elseif ($data['role'] === 'mentor') {
                Mentor::create([
                    'user_id' => $user->id,
                    'cv_path' => $data['cv_path'] ?? null,
                    'bidang_keahlian' => $data['bidang_keahlian'] ?? null,
                    'pengalaman' => $data['pengalaman'] ?? null,
                    'verification_status' => 'pending',
                ]);
            }

            return $user->load(['pelajar', 'mentor']);
        });
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Check if mentor is verified
        if ($user->isMentor() && $user->mentor && !$user->mentor->isVerified()) {
            return [
                'error' => 'Akun masih menunggu persetujuan Admin'
            ];
        }

        // Check if user is active
        if (!$user->is_active) {
            return [
                'error' => 'Akun Anda telah dinonaktifkan'
            ];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Award daily login streak XP (only for pelajar)
        if ($user->isPelajar() && $user->pelajar) {
            $this->awardDailyLoginXP($user->id);
        }

        return [
            'user' => $user->load(['pelajar', 'mentor']),
            'token' => $token,
        ];
    }

    /**
     * Award daily login streak XP
     */
    protected function awardDailyLoginXP(int $userId): void
    {
        $lastLogin = \App\Models\ActivityLog::where('user_id', $userId)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->first();

        $today = now()->startOfDay();
        $lastLoginDate = $lastLogin ? $lastLogin->created_at->startOfDay() : null;

        // If last login was not today, award XP
        if (!$lastLoginDate || $lastLoginDate->lt($today)) {
            try {
                $gamificationService = app(\App\Services\GamificationService::class);
                $gamificationService->addXP($userId, 5, 'Daily login streak');
                
                // Log login activity
                \App\Models\ActivityLog::log('login', $userId);
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}

