<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get chat rooms
     */
    public function index(Request $request)
    {
        try {
            $chatRooms = $this->chatService->getChatRooms($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $chatRooms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages with a user
     */
    public function messages(Request $request, $userId)
    {
        try {
            $limit = $request->get('limit', 50);
            $messages = $this->chatService->getMessages(
                $request->user()->id,
                $userId,
                $limit
            );

            // Mark as read
            $this->chatService->markAsRead($request->user()->id, $userId);

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message
     */
    public function send(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $message = $this->chatService->sendMessage(
                $request->user()->id,
                $userId,
                $request->message
            );

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim',
                'data' => $message->load(['sender', 'receiver'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mark messages as read
     */
    public function markRead(Request $request, $userId)
    {
        try {
            $this->chatService->markAsRead($request->user()->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Pesan ditandai sudah dibaca'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

