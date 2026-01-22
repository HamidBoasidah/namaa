<?php

namespace App\Policies;

use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can download the attachment.
     * 
     * User can download attachment if they are a participant in the conversation
     * containing the message.
     * 
     * @param User $user
     * @param MessageAttachment $attachment
     * @return bool
     */
    public function download(User $user, MessageAttachment $attachment): bool
    {
        return $attachment->message->conversation->isParticipant($user->id);
    }
}
