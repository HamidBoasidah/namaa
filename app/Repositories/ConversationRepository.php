<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Support\Facades\DB;

class ConversationRepository extends BaseRepository
{
    protected array $defaultWith = [
        'booking',
        'participants',
    ];

    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    /**
     * Find conversation by booking ID
     *
     * @param int $bookingId
     * @return Conversation|null
     */
    public function findByBooking(int $bookingId): ?Conversation
    {
        return $this->makeQuery()->where('booking_id', $bookingId)->first();
    }

    /**
     * Create conversation with participants
     * Uses DB transaction to ensure atomicity
     *
     * @param int $bookingId
     * @param int $clientId
     * @param int $consultantId
     * @return Conversation
     */
    public function createWithParticipants(int $bookingId, int $clientId, int $consultantId): Conversation
    {
        return DB::transaction(function () use ($bookingId, $clientId, $consultantId) {
            // Create the conversation
            /** @var Conversation $conversation */
            $conversation = $this->create([
                'booking_id' => $bookingId,
            ]);

            // Attach both participants
            $conversation->participants()->attach([$clientId, $consultantId]);

            // Reload with relationships
            return $conversation->load(['booking', 'participants']);
        });
    }

    /**
     * Check if user is participant in conversation
     *
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function isParticipant(int $conversationId, int $userId): bool
    {
        return DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();
    }
}
