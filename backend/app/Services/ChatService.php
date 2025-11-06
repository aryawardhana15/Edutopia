<?php

namespace App\Services;

use App\Models\Message;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\DB;

class ChatService
{
    /**
     * Get or create chat room between pelajar and mentor
     */
    public function getOrCreateChatRoom(int $pelajarId, int $mentorId): array
    {
        // Verify that pelajar is enrolled in at least one course by this mentor
        $enrollment = CourseEnrollment::whereHas('course', function ($query) use ($mentorId) {
            $query->where('mentor_id', $mentorId);
        })->where('pelajar_id', $pelajarId)->first();

        if (!$enrollment) {
            throw new \Exception('Anda belum terdaftar di kursus mentor ini');
        }

        return [
            'pelajar_id' => $pelajarId,
            'mentor_id' => $mentorId,
        ];
    }

    /**
     * Get chat rooms for user
     */
    public function getChatRooms(int $userId): array
    {
        $sentMessages = Message::where('sender_id', $userId)
            ->select('receiver_id')
            ->distinct()
            ->get()
            ->pluck('receiver_id');

        $receivedMessages = Message::where('receiver_id', $userId)
            ->select('sender_id')
            ->distinct()
            ->get()
            ->pluck('sender_id');

        $chatUserIds = $sentMessages->merge($receivedMessages)->unique();

        $chatRooms = [];
        foreach ($chatUserIds as $chatUserId) {
            $otherUser = \App\Models\User::find($chatUserId);
            if ($otherUser) {
                $lastMessage = Message::where(function ($query) use ($userId, $chatUserId) {
                    $query->where('sender_id', $userId)
                          ->where('receiver_id', $chatUserId);
                })->orWhere(function ($query) use ($userId, $chatUserId) {
                    $query->where('sender_id', $chatUserId)
                          ->where('receiver_id', $userId);
                })->orderBy('created_at', 'desc')->first();

                $unreadCount = Message::where('sender_id', $chatUserId)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $chatRooms[] = [
                    'user' => $otherUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                ];
            }
        }

        return $chatRooms;
    }

    /**
     * Get messages between two users
     */
    public function getMessages(int $userId, int $otherUserId, int $limit = 50)
    {
        return Message::where(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $otherUserId);
        })->orWhere(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $otherUserId)
                  ->where('receiver_id', $userId);
        })->orderBy('created_at', 'desc')
          ->limit($limit)
          ->get()
          ->reverse()
          ->values();
    }

    /**
     * Send message
     */
    public function sendMessage(int $senderId, int $receiverId, string $message): Message
    {
        // Verify chat room exists (pelajar-mentor relationship)
        if (\App\Models\User::find($senderId)->isPelajar() && 
            \App\Models\User::find($receiverId)->isMentor()) {
            $this->getOrCreateChatRoom($senderId, $receiverId);
        } elseif (\App\Models\User::find($senderId)->isMentor() && 
                   \App\Models\User::find($receiverId)->isPelajar()) {
            $this->getOrCreateChatRoom($receiverId, $senderId);
        }

        return Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $userId, int $otherUserId): void
    {
        Message::where('sender_id', $otherUserId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}

