<?php

namespace App\Http\Controllers\Api;

use App\DTOs\MarkReadDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Booking;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Services\ReadStateService;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    protected ChatService $chatService;
    protected ReadStateService $readStateService;

    public function __construct(ChatService $chatService, ReadStateService $readStateService)
    {
        $this->middleware('auth:sanctum');
        $this->chatService = $chatService;
        $this->readStateService = $readStateService;
    }

    /**
     * Get or create conversation for a booking
     * GET /api/bookings/{booking}/conversation
     * 
     * @param Booking $booking
     * @return JsonResponse
     */
    public function getOrCreate(Booking $booking): JsonResponse
    {
        // Authorize: user must be client or consultant for this booking
        $this->authorize('view', $booking);

        // Delegate to ChatService to get or create conversation
        // The service returns a ConversationDTO which already has all the data we need
        $conversationDTO = $this->chatService->getOrCreateConversation(
            $booking->id,
            auth()->id()
        );

        // Get the conversation ID from the DTO
        $conversationId = $conversationDTO->toArray()['id'];

        // Load the conversation model with relationships for the resource
        $conversation = \App\Models\Conversation::with(['participants', 'booking'])
            ->find($conversationId);

        // Return ConversationResource
        return $this->resourceResponse(
            new ConversationResource($conversation),
            'تم جلب المحادثة بنجاح'
        );
    }

    /**
     * Get user's conversations list with unread counts
     * 
     * Returns a paginated list of conversations for the authenticated user,
     * including unread message counts for each conversation.
     * 
     * Endpoint: GET /api/conversations
     * Authentication: Required (sanctum)
     * 
     * Query Parameters:
     * - search (optional): Search term to filter conversations
     * - per_page (optional): Number of conversations per page (default: 20)
     * 
     * Response Structure:
     * {
     *   "success": true,
     *   "message": "تم جلب المحادثات بنجاح",
     *   "status_code": 200,
     *   "data": [
     *     {
     *       "id": 1,
     *       "booking_id": 123,
     *       "other_participant": {...},
     *       "last_message": {...},
     *       "unread_count": 3,
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     }
     *   ]
     * }
     * 
     * @param \App\Http\Requests\Api\GetConversationsRequest $request
     * @return JsonResponse
     */
    public function index(\App\Http\Requests\Api\GetConversationsRequest $request): JsonResponse
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 20);

        $conversations = $this->chatService->getUserConversations(
            auth()->id(),
            $search,
            $perPage
        );

        // Check if no conversations found
        if ($conversations->isEmpty()) {
            return $this->successResponse(
                [],
                'لا توجد محادثات بعد',
                200
            );
        }

        return $this->resourceResponse(
            \App\Http\Resources\ConversationListResource::collection($conversations),
            'تم جلب المحادثات بنجاح'
        );
    }

    /**
     * Mark conversation as read
     * 
     * Explicitly marks a conversation as read for the authenticated user.
     * Updates the read marker to the specified message ID or the latest message.
     * 
     * Endpoint: POST /api/conversations/{conversation}/read
     * Authentication: Required (sanctum)
     * Authorization: User must be a participant in the conversation
     * 
     * Request Body (optional):
     * {
     *   "message_id": 123  // Optional: specific message to mark as read up to
     * }
     * 
     * Response Structure:
     * {
     *   "success": true,
     *   "message": "تم تحديث حالة القراءة بنجاح",
     *   "status_code": 200,
     *   "data": {
     *     "unread_count": 0
     *   }
     * }
     * 
     * Race Condition Handling:
     * - Uses optimistic approach: captures latest message ID at call time
     * - If new messages arrive during operation, they remain unread (correct behavior)
     * - Atomic single-row UPDATE prevents data corruption
     * 
     * @param Conversation $conversation The conversation to mark as read
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function markAsRead(Conversation $conversation, \Illuminate\Http\Request $request): JsonResponse
    {
        // Authorize: user must be participant in conversation
        $this->authorize('view', $conversation);

        $dto = MarkReadDTO::fromRequest($request, $conversation->id);
        $success = $this->readStateService->markAsRead($dto);

        return response()->json([
            'success' => $success,
            'message' => 'تم تحديث حالة القراءة بنجاح',
            'status_code' => 200,
            'data' => [
                'unread_count' => $this->readStateService->getUnreadCount(
                    $conversation->id,
                    $request->user()->id
                )
            ]
        ]);
    }
}
