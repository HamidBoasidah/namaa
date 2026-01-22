<?php

namespace App\Services;

use App\DTOs\ConversationDTO;
use App\DTOs\MessageDTO;
use App\Exceptions\BusinessLogicException;
use App\Exceptions\ForbiddenException;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChatService
{
    protected ConversationRepository $conversations;
    protected MessageRepository $messages;
    protected AttachmentService $attachments;

    public function __construct(
        ConversationRepository $conversations,
        MessageRepository $messages,
        AttachmentService $attachments
    ) {
        $this->conversations = $conversations;
        $this->messages = $messages;
        $this->attachments = $attachments;
    }

    /**
     * Get or create conversation for a booking
     * Returns existing conversation if one exists, otherwise creates new one
     * 
     * @param int $bookingId
     * @param int $userId
     * @return ConversationDTO
     * @throws BusinessLogicException
     */
    public function getOrCreateConversation(int $bookingId, int $userId): ConversationDTO
    {
        // Find booking with relationships
        $booking = Booking::with(['client', 'consultant.user'])->findOrFail($bookingId);

        // Verify user is participant (client or consultant's user)
        $consultantUserId = $booking->consultant->user_id;
        if ($booking->client_id !== $userId && $consultantUserId !== $userId) {
            throw new ForbiddenException('أنت لست مشاركاً في هذا الحجز');
        }

        // Check if conversation already exists
        $conversation = $this->conversations->findByBooking($bookingId);

        if ($conversation) {
            return ConversationDTO::fromModel($conversation);
        }

        // Create new conversation with participants (client and consultant's user)
        $conversation = $this->conversations->createWithParticipants(
            $bookingId,
            $booking->client_id,
            $consultantUserId
        );

        return ConversationDTO::fromModel($conversation);
    }

    /**
     * Send a message with optional attachments
     * Enforces all business rules: status, limits, session context
     * Uses DB transaction + locking for race condition prevention
     * 
     * @param int $conversationId
     * @param int $senderId
     * @param string|null $body
     * @param array $uploadedFiles Array of UploadedFile instances
     * @return MessageDTO
     * @throws ForbiddenException
     * @throws BusinessLogicException
     */
    public function sendMessage(
        int $conversationId,
        int $senderId,
        ?string $body,
        array $uploadedFiles
    ): MessageDTO {
        return DB::transaction(function () use ($conversationId, $senderId, $body, $uploadedFiles) {
            // Lock conversation to prevent race conditions
            $conversation = Conversation::with(['booking.consultant', 'participants'])
                ->lockForUpdate()
                ->findOrFail($conversationId);

            // Verify user is participant
            if (!$conversation->isParticipant($senderId)) {
                throw new ForbiddenException('أنت لست مشاركاً في هذه المحادثة');
            }

            // Verify booking status is confirmed
            if ($conversation->booking->status !== Booking::STATUS_CONFIRMED) {
                throw new ForbiddenException('المراسلة متاحة فقط للحجوزات المؤكدة');
            }

            // Determine if we're in session
            $isInSession = $this->isInSession($conversation->booking);
            $context = $isInSession ? 'in_session' : 'out_of_session';

            // Check if user can send message (enforces client limits)
            if (!$this->canSendMessage($conversation, $senderId, $isInSession)) {
                throw new ForbiddenException('لقد وصلت للحد الأقصى من الرسائل (رسالتان) خارج وقت الجلسة');
            }

            // Validate files if present
            if (!empty($uploadedFiles)) {
                $this->attachments->validateFiles($uploadedFiles);
            }

            // Determine message type
            $type = $this->determineMessageType($body, $uploadedFiles);

            // Create message
            $message = $this->messages->create([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'type' => $type,
                'context' => $context,
            ]);

            // Store attachments if present
            if (!empty($uploadedFiles)) {
                $this->attachments->storeAttachments($message->id, $uploadedFiles);
                // Reload message with attachments
                $message = $message->fresh(['sender', 'attachments']);
            }

            return MessageDTO::fromModel($message);
        });
    }

    /**
     * Determine if current time is within session window
     * Session: [start_at, start_at + duration_minutes]
     * Buffer time is excluded from session window
     * 
     * @param Booking $booking
     * @return bool
     */
    public function isInSession(Booking $booking): bool
    {
        $now = now();
        $sessionStart = $booking->start_at;
        $sessionEnd = $booking->start_at->copy()->addMinutes($booking->duration_minutes);

        return $now->gte($sessionStart) && $now->lt($sessionEnd);
    }

    /**
     * Count client's out-of-session messages in a conversation
     * Used for enforcing 2-message limit
     * 
     * @param int $conversationId
     * @param int $clientId
     * @return int
     */
    public function countClientOutOfSessionMessages(int $conversationId, int $clientId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->where('sender_id', $clientId)
            ->where('context', 'out_of_session')
            ->count();
    }

    /**
     * Validate if user can send message
     * Checks: booking status, participant authorization, client limits
     * 
     * @param Conversation $conversation
     * @param int $senderId
     * @param bool $isInSession
     * @return bool
     */
    public function canSendMessage(Conversation $conversation, int $senderId, bool $isInSession): bool
    {
        // Must be participant
        if (!$conversation->isParticipant($senderId)) {
            return false;
        }

        // Booking must be confirmed (already checked in sendMessage, but included for completeness)
        if ($conversation->booking->status !== Booking::STATUS_CONFIRMED) {
            return false;
        }

        // If in session, unlimited messages allowed
        if ($isInSession) {
            return true;
        }

        // Out of session: check if sender is consultant's user
        $consultantUserId = $conversation->booking->consultant->user_id;
        $isConsultant = $senderId === $consultantUserId;
        
        // Consultants have unlimited out-of-session messages
        if ($isConsultant) {
            return true;
        }

        // Client: check out-of-session message limit (max 2)
        $count = $this->messages->countOutOfSessionWithLock(
            $conversation->id,
            $senderId
        );

        return $count < 2;
    }

    /**
     * Determine message type based on content
     * 
     * @param string|null $body
     * @param array $uploadedFiles
     * @return string 'text', 'attachment', or 'mixed'
     */
    protected function determineMessageType(?string $body, array $uploadedFiles): string
    {
        $hasBody = !empty($body);
        $hasFiles = !empty($uploadedFiles);

        if ($hasBody && $hasFiles) {
            return 'mixed';
        }

        if ($hasFiles) {
            return 'attachment';
        }

        return 'text';
    }
}
