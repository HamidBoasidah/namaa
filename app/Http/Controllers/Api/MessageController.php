<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetMessagesRequest;
use App\Http\Requests\Api\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->middleware('auth:sanctum');
        $this->chatService = $chatService;
    }

    /**
     * List messages in a conversation with cursor pagination
     * 
     * Fetches paginated messages for a conversation and implicitly marks the
     * conversation as read up to the latest fetched message.
     * 
     * Endpoint: GET /api/conversations/{conversation}/messages
     * Authentication: Required (sanctum)
     * Authorization: User must be a participant in the conversation
     * 
     * Query Parameters:
     * - per_page (optional): Number of messages per page (default: 50)
     * - cursor (optional): Cursor for pagination
     * 
     * Response Structure:
     * {
     *   "success": true,
     *   "message": "تم جلب الرسائل بنجاح",
     *   "status_code": 200,
     *   "data": [
     *     {
     *       "id": 1,
     *       "conversation_id": 123,
     *       "sender_id": 456,
     *       "body": "Hello",
     *       "type": "text",
     *       "context": "in_session",
     *       "created_at": "2024-01-01T00:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "next_cursor": "...",
     *     "prev_cursor": "...",
     *     "per_page": 50,
     *     "unread_count": 0
     *   }
     * }
     * 
     * Implicit Mark as Read:
     * - Automatically updates read marker to latest fetched message
     * - Returns updated unread_count in meta
     * - Uses optimistic approach for race condition handling
     * 
     * @param Conversation $conversation
     * @param GetMessagesRequest $request
     * @return JsonResponse
     */
    public function index(Conversation $conversation, GetMessagesRequest $request): JsonResponse
    {
        // Authorize: user must be participant in conversation
        $this->authorize('view', $conversation);

        // Get pagination parameters
        $perPage = $request->input('per_page', config('chat.pagination.messages_per_page', 50));
        $cursor = $request->input('cursor');

        // Get messages and mark as read
        $result = $this->chatService->getMessagesAndMarkRead(
            conversationId: $conversation->id,
            userId: $request->user()->id,
            perPage: $perPage,
            cursor: $cursor
        );

        // Transform to resource collection
        $data = MessageResource::collection($result['messages']);

        // Build response with pagination metadata and unread count
        $response = [
            'success' => true,
            'message' => 'تم جلب الرسائل بنجاح',
            'status_code' => 200,
            'data' => $data,
            'meta' => [
                'next_cursor' => $result['messages']->nextCursor()?->encode(),
                'prev_cursor' => $result['messages']->previousCursor()?->encode(),
                'per_page' => $result['messages']->perPage(),
                'unread_count' => $result['unread_count'],
            ],
        ];

        return response()->json($response, 200);
    }

    /**
     * Send a message with optional attachments
     * POST /api/conversations/{conversation}/messages
     * 
     * @param Conversation $conversation
     * @param SendMessageRequest $request
     * @return JsonResponse
     */
    public function store(Conversation $conversation, SendMessageRequest $request): JsonResponse
    {
        // Authorize: user must be able to send message in this conversation
        $this->authorize('sendMessage', $conversation);

        // Get validated data
        $body = $request->input('body');
        $files = $request->file('files', []);

        // Delegate to ChatService to send message
        // Service handles all business logic: limits, context, attachments
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            auth()->id(),
            $body,
            $files
        );

        // Get the message ID from the DTO array
        $messageData = $messageDTO->toArray();
        $messageId = $messageData['id'];

        // Load the message model with relationships for the resource
        $message = \App\Models\Message::with(['sender', 'attachments'])
            ->find($messageId);

        // Return MessageResource with 201 Created status
        return $this->createdResponse(
            new MessageResource($message),
            'تم إرسال الرسالة بنجاح'
        );
    }
}
