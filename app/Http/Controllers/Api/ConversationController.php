<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Booking;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->middleware('auth:sanctum');
        $this->chatService = $chatService;
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
}
