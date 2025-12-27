<?php

namespace App\Services;

use App\Models\Kyc;
use App\Repositories\KycRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException as AppValidationException;
use App\Services\MailService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KycService
{
    protected KycRepository $kycs;

    public function __construct(KycRepository $kycs)
    {
        $this->kycs = $kycs;
    }

    /**
     * Generic query builder (useful for special cases)
     */
    public function query(?array $with = null): Builder
    {
        return $this->kycs->query($with);
    }

    /**
     * Get all records. $with follows the BaseRepository convention:
     * - null => use repository defaultWith
     * - []   => no relations
     * - ['rel'] => specific relations
     */
    public function all(?array $with = null)
    {
        return $this->kycs->all($with);
    }

    public function paginate(int $perPage = 15, ?array $with = null)
    {
        return $this->kycs->paginate($perPage, $with);
    }

    public function find(int|string $id, ?array $with = null)
    {
        return $this->kycs->findOrFail($id, $with);
    }

    /**
     * Create a new KYC. In API use-cases we auto-assign the authenticated user id
     * when it's not provided. Also protect against concurrent creations that would
     * result in multiple pending/approved KYCs for the same user.
     */
    public function create(array $attributes)
    {
        // Inject authenticated user id if missing (same behaviour as AddressService)
        if (empty($attributes['user_id']) && Auth::check()) {
            $attributes['user_id'] = Auth::id();
        }

        $userId = $attributes['user_id'] ?? null;

        return DB::transaction(function () use ($attributes, $userId) {
            if ($userId) {
                // lock the user row to prevent concurrent creations for the same user
                \App\Models\User::where('id', $userId)->lockForUpdate()->first();

                $exists = Kyc::where('user_id', $userId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($exists) {
                    throw AppValidationException::withMessages([
                        'user_id' => __('validation.kyc.errors.already_has_pending_or_approved'),
                    ]);
                }
            }

            return $this->kycs->create($attributes);
        });
    }

    /**
     * Update by id (admin/back-office friendly)
     */
    public function update(int|string $id, array $attributes)
    {
        return $this->kycs->update($id, $attributes);
    }

    /**
     * Update an already-loaded KYC model (API-friendly path) and return the updated model.
     */
    public function updateModel(Model $kyc, array $attributes)
    {
        return $this->kycs->updateModel($kyc, $attributes);
    }

    public function delete(int|string $id)
    {
        return $this->kycs->delete($id);
    }

    public function streamDocument(Kyc $kyc, bool $download = false): StreamedResponse
    {
        if (!$kyc->document_scan_copy) {
            abort(404, __('Document not found.'));
        }

        $disk = Storage::disk('local');
        $path = $kyc->document_scan_copy;

        // Ensure the directory exists; create it lazily if missing so subsequent
        // uploads/reads do not fail when the folder hasn't been created yet.
        $directory = trim(str_replace('\\', '/', dirname($path)), '/.');
        if ($directory !== '' && !$disk->exists($directory)) {
            $disk->makeDirectory($directory, 0755, true);
        }

        if (!$disk->exists($path)) {
            abort(404, __('Document not found.'));
        }

        $fileName = basename($path);
        $absolutePath = $disk->path($path);
        $mimeType = @mime_content_type($absolutePath) ?: 'application/octet-stream';
        $stream = $disk->readStream($path);

        if ($stream === false) {
            abort(500, __('Unable to read document.'));
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => sprintf('%s; filename="%s"', $download ? 'attachment' : 'inline', $fileName),
        ]);
    }

    /**
     * 🔹 API: Query for a specific user's KYCs (supports filters via CanFilter)
     */
    public function getQueryForUser(int $userId, ?array $with = null): Builder
    {
        return $this->kycs->forUser($userId, $with);
    }

    public function allForUser(int $userId, ?array $with = null)
    {
        return $this->kycs->allForUser($userId, $with);
    }

    public function paginateForUser(int $userId, int $perPage = 15, ?array $with = null)
    {
        return $this->kycs->paginateForUser($userId, $perPage, $with);
    }

    public function findForUser(int|string $id, int $userId, ?array $with = null)
    {
        return $this->kycs->findForUser($id, $userId, $with);
    }

    /**
     * Notify the KYC owner by email about status changes (approved/rejected).
     * Accepts optional incoming data (e.g. rejected_reason) as a fallback.
     */
    public function notifyUserStatus(Kyc $kyc, array $data = []): void
    {
        // Ensure we have the freshest values
        $kyc->refresh();

        if ($kyc->status === 'approved') {
            MailService::send(
                to: $kyc->user->email,
                subject: 'تمت الموافقة على طلب التحقق',
                body: 'تهانينا! تمت الموافقة على طلب التحقق الخاص بك.'
            );
            return;
        }

        if ($kyc->status === 'rejected') {
            $reason = $kyc->rejected_reason ?? ($data['rejected_reason'] ?? null);

            $body = 'نأسف! تم رفض طلب التحقق الخاص بك.';
            if (!empty($reason)) {
                $body .= '<br><br><strong>سبب الرفض:</strong> ' . e($reason);
            }

            MailService::send(
                to: $kyc->user->email,
                subject: 'تم رفض طلب التحقق',
                body: $body
            );
        }
    }

}
