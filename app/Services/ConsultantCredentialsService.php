<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consultant;
use App\Models\Certificate;
use App\Models\ConsultantExperience;
use Illuminate\Http\UploadedFile;
use App\Support\FilenameHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ConsultantCredentialsService
{
    /**
     * Get all certificates and experiences for a consultant
     */
    public function getCredentials(User $user): array
    {
        $consultant = Consultant::where('user_id', $user->id)->first();

        if (!$consultant) {
            return [
                'certificates' => collect(),
                'experiences' => collect(),
            ];
        }

        $certificates = Certificate::where('consultant_id', $consultant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $experiences = ConsultantExperience::where('consultant_id', $consultant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'certificates' => $certificates,
            'experiences' => $experiences,
        ];
    }

    /**
     * Add a new certificate for the consultant
     */
    public function addCertificate(User $user, array $data): Certificate
    {
        $consultant = $this->getOrCreateConsultant($user);

        $data['consultant_id'] = $consultant->id;
        $data['status'] = 'pending';
        $data['is_verified'] = false;

        // Handle file upload
        if (isset($data['document_scan_copy']) && $data['document_scan_copy'] instanceof UploadedFile) {
            $file = $data['document_scan_copy'];
            $originalName = $file->getClientOriginalName();
            $displayName = FilenameHelper::sanitizeForDisplay($originalName);
            $displayName = FilenameHelper::ensureValidName($displayName);

            $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('certificates', $filename, 'local');

            $data['document_scan_copy'] = $path;
            $data['document_scan_copy_original_name'] = $displayName;
        }

        return Certificate::create($data);
    }

    /**
     * Delete a certificate
     */
    public function deleteCertificate(Certificate $certificate): bool
    {
        // Delete the file if exists
        if ($certificate->document_scan_copy) {
            Storage::disk('local')->delete($certificate->document_scan_copy);
        }

        return $certificate->delete();
    }

    /**
     * Add a new experience for the consultant
     */
    public function addExperience(User $user, array $data): ConsultantExperience
    {
        $consultant = $this->getOrCreateConsultant($user);

        $data['consultant_id'] = $consultant->id;
        $data['is_active'] = $data['is_active'] ?? true;

        return ConsultantExperience::create($data);
    }

    /**
     * Delete an experience
     */
    public function deleteExperience(ConsultantExperience $experience): bool
    {
        return $experience->delete();
    }

    /**
     * Update an existing experience
     */
    public function updateExperience(ConsultantExperience $experience, array $data): ConsultantExperience
    {
        $experience->fill($data);
        $experience->save();

        return $experience;
    }

    /**
     * Get consultant by user
     */
    public function getConsultantByUser(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * Get or create consultant for user
     */
    protected function getOrCreateConsultant(User $user): Consultant
    {
        $consultant = Consultant::where('user_id', $user->id)->first();

        if (!$consultant) {
            $consultant = Consultant::create([
                'user_id' => $user->id,
                'is_active' => true,
            ]);
        }

        return $consultant;
    }
}
