<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Kyc;

class KycPolicy
{
    /**
     * Any authenticated user can view their own KYCs in listings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create a KYC (business rules are handled in service).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Users can view their own KYC.
     */
    public function view(User $user, Kyc $kyc): bool
    {
        return $kyc->user_id === $user->id;
    }

    /**
     * Users can update their own KYC (service may restrict by status).
     */
    public function update(User $user, Kyc $kyc): bool
    {
        return $kyc->user_id === $user->id;
    }

    /**
     * Users may delete their own KYC.
     */
    public function delete(User $user, Kyc $kyc): bool
    {
        return $kyc->user_id === $user->id;
    }

    /**
     * Stream/download permissions: only owner may access the document.
     */
    public function viewDocument(User $user, Kyc $kyc): bool
    {
        return $kyc->user_id === $user->id;
    }

    public function downloadDocument(User $user, Kyc $kyc): bool
    {
        return $kyc->user_id === $user->id;
    }
}
