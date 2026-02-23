<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consultant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Support\FilenameHelper;
use Illuminate\Support\Str;

class ConsultantProfileService
{
    /**
     * Get consultant profile with all related data
     */
    public function getProfile(User $user): array
    {
        $consultant = Consultant::where('user_id', $user->id)
            ->with('consultationType')
            ->first();

        return [
            'user' => $user,
            'consultant' => $consultant,
        ];
    }

    /**
     * Update consultant profile (user + consultant data)
     */
    public function updateProfile(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            // Separate user and consultant data
            $userData = $this->extractUserData($data);
            $consultantData = $this->extractConsultantData($data);

            // Handle avatar upload
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $userData['avatar'] = $this->handleAvatarUpload($data['avatar'], $user->avatar);
            }

            // Update user
            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update or create consultant record
            $consultant = null;
            if (!empty($consultantData)) {
                $consultant = Consultant::updateOrCreate(
                    ['user_id' => $user->id],
                    $consultantData
                );
                $consultant->load('consultationType');
            } else {
                $consultant = Consultant::where('user_id', $user->id)
                    ->with('consultationType')
                    ->first();
            }

            return [
                'user' => $user->fresh(),
                'consultant' => $consultant,
            ];
        });
    }

    /**
     * Delete consultant account (soft delete user and consultant)
     */
    public function deleteAccount(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Soft delete consultant record if exists
            Consultant::where('user_id', $user->id)->delete();

            // Soft delete user
            $user->delete();

            // Revoke all tokens
            $user->tokens()->delete();

            return true;
        });
    }

    /**
     * Extract user-related fields from data
     */
    protected function extractUserData(array $data): array
    {
        $userFields = [
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'gender',
            'locale',
        ];

        return array_filter(
            array_intersect_key($data, array_flip($userFields)),
            fn($value) => $value !== null
        );
    }

    /**
     * Extract consultant-related fields from data
     */
    protected function extractConsultantData(array $data): array
    {
        $consultantFields = [
            'years_of_experience',
            'consultation_type_id',
            'price',
            'duration_minutes',
            'buffer',
        ];

        return array_filter(
            array_intersect_key($data, array_flip($consultantFields)),
            fn($value) => $value !== null
        );
    }

    /**
     * Handle avatar upload with old file cleanup
     */
    protected function handleAvatarUpload(UploadedFile $file, ?string $oldPath): string
    {
        // Delete old avatar if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new avatar with UUID
        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('avatars', $filename, 'public');
    }
}
