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
     * GET /api/conversations/{conversation}/messages
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

        // Get paginated messages from repository
        $messages = app(\App\Repositories\MessageRepository::class)
            ->paginateMessages($conversation->id, $perPage, $cursor);

        // Transform to resource collection
        $data = MessageResource::collection($messages);

        // Build response with pagination metadata
        $response = [
            'success' => true,
            'message' => 'تم جلب الرسائل بنجاح',
            'status_code' => 200,
            'data' => $data,
            'meta' => [
                'next_cursor' => $messages->nextCursor()?->encode(),
                'prev_cursor' => $messages->previousCursor()?->encode(),
                'per_page' => $messages->perPage(),
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
