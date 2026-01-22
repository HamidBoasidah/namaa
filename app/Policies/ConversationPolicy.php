<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the conversation.
     * 
     * User can view conversation if they are a participant
     * (either the client or consultant for that booking).
     * 
     * @param User $user
     * @param Conversation $conversation
     * @return bool
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->isParticipant($user->id);
    }

    /**
     * Determine if the user can send a message in the conversation.
     * 
     * User can send message if:
     * - They are a participant in the conversation
     * - The booking status is 'confirmed'
     * 
     * @param User $user
     * @param Conversation $conversation
     * @return bool
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        // Must be a participant
        if (!$conversation->isParticipant($user->id)) {
            return false;
        }

        // Booking must be confirmed
        if ($conversation->booking->status !== Booking::STATUS_CONFIRMED) {
            return false;
        }

        return true;
    }
}

