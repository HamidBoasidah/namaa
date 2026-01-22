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

    /**
     * Get all conversations for a user with pagination and search
     *
     * @param int $userId
     * @param string|null $search
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserConversations(int $userId, ?string $search = null, int $perPage = 20)
    {
        $query = $this->model
            ->select('conversations.*')
            ->join('conversation_participants', 'conversations.id', '=', 'conversation_participants.conversation_id')
            ->where('conversation_participants.user_id', $userId)
            ->with([
                'booking.client',
                'booking.consultant.user',
                'participants',
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->orderBy('conversations.updated_at', 'desc');

        // Search in participant names if search term provided
        if ($search) {
            $query->where(function ($q) use ($search, $userId) {
                $q->whereHas('participants', function ($participantQuery) use ($search, $userId) {
                    $participantQuery->where('users.id', '!=', $userId)
                        ->where(function ($nameQuery) use ($search) {
                            $nameQuery->where('users.first_name', 'like', "%{$search}%")
                                ->orWhere('users.last_name', 'like', "%{$search}%")
                                ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%{$search}%");
                        });
                });
            });
        }

        return $query->paginate($perPage);
    }
}
