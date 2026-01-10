<?php

namespace App\Services;

use App\Models\Certificate;
use App\Repositories\CertificateRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException as AppValidationException;
use App\Services\MailService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateService
{
    protected CertificateRepository $certificates;

    public function __construct(CertificateRepository $certificates)
    {
        $this->certificates = $certificates;
    }

    /**
     * Generic query builder (useful for special cases)
     */
    public function query(?array $with = null): Builder
    {
        return $this->certificates->query($with);
    }

    /**
     * Get all records. $with follows the BaseRepository convention:
     * - null => use repository defaultWith
     * - []   => no relations
     * - ['rel'] => specific relations
     */
    public function all(?array $with = null)
    {
        return $this->certificates->all($with);
    }

    public function paginate(int $perPage = 15, ?array $with = null)
    {
        return $this->certificates->paginate($perPage, $with);
    }

    public function find(int|string $id, ?array $with = null)
    {
        return $this->certificates->findOrFail($id, $with);
    }

    /**
     * Create a new Certificate. In API use-cases we auto-assign the consultant id
     * when it's not provided.
     */
    public function create(array $attributes)
    {
        // Inject authenticated consultant id if missing
        if (empty($attributes['consultant_id']) && Auth::check()) {
            $consultant = \App\Models\Consultant::where('user_id', Auth::id())->first();
            if ($consultant) {
                $attributes['consultant_id'] = $consultant->id;
            }
        }

        return $this->certificates->create($attributes);
    }

    /**
     * Update by id (admin/back-office friendly)
     */
    public function update(int|string $id, array $attributes)
    {
        return $this->certificates->update($id, $attributes);
    }

    /**
     * Update an already-loaded Certificate model (API-friendly path) and return the updated model.
     */
    public function updateModel(Model $certificate, array $attributes)
    {
        return $this->certificates->updateModel($certificate, $attributes);
    }

    public function delete(int|string $id)
    {
        return $this->certificates->delete($id);
    }

    public function streamDocument(Certificate $certificate, bool $download = false): StreamedResponse
    {
        if (!$certificate->document_scan_copy) {
            abort(404, __('Document not found.'));
        }

        $disk = Storage::disk('local');
        $path = $certificate->document_scan_copy;

        // Ensure the directory exists; create it lazily if missing so subsequent
        // uploads/reads do not fail when the folder hasn't been created yet.
        $directory = trim(str_replace('\\', '/', dirname($path)), '/.');
        if ($directory !== '' && !$disk->exists($directory)) {
            $disk->makeDirectory($directory, 0755, true);
        }

        if (!$disk->exists($path)) {
            abort(404, __('Document not found.'));
        }

        // Use original filename for download, fallback to stored filename
        $downloadName = $certificate->document_scan_copy_original_name
            ?? basename($path);

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
            // Support UTF-8 filenames (RFC 5987) for Arabic and other non-ASCII characters
            'Content-Disposition' => sprintf(
                '%s; filename="%s"; filename*=UTF-8\'\'%s',
                $download ? 'attachment' : 'inline',
                $downloadName,
                rawurlencode($downloadName)
            ),
        ]);
    }

    /**
     * 🔹 API: Query for a specific consultant's Certificates (supports filters via CanFilter)
     */
    public function getQueryForConsultant(int $consultantId, ?array $with = null): Builder
    {
        return $this->certificates->forConsultant($consultantId, $with);
    }

    public function allForConsultant(int $consultantId, ?array $with = null)
    {
        return $this->certificates->allForConsultant($consultantId, $with);
    }

    public function paginateForConsultant(int $consultantId, int $perPage = 15, ?array $with = null)
    {
        return $this->certificates->paginateForConsultant($consultantId, $perPage, $with);
    }

    public function findForConsultant(int|string $id, int $consultantId, ?array $with = null)
    {
        return $this->certificates->findForConsultant($id, $consultantId, $with);
    }

    /**
     * Notify the Certificate owner by email about status changes (approved/rejected).
     * Accepts optional incoming data (e.g. rejected_reason) as a fallback.
     */
    public function notifyUserStatus(Certificate $certificate, array $data = []): void
    {
        // Ensure we have the freshest values
        $certificate->refresh();

        $user = $certificate->consultant?->user;
        if (!$user) {
            return;
        }

        if ($certificate->status === 'approved') {
            MailService::send(
                to: $user->email,
                subject: 'تمت الموافقة على طلب التحقق',
                body: 'تهانينا! تمت الموافقة على طلب التحقق الخاص بك.'
            );
            return;
        }

        if ($certificate->status === 'rejected') {
            $reason = $certificate->rejected_reason ?? ($data['rejected_reason'] ?? null);

            $body = 'نأسف! تم رفض طلب التحقق الخاص بك.';
            if (!empty($reason)) {
                $body .= '<br><br><strong>سبب الرفض:</strong> ' . e($reason);
            }

            MailService::send(
                to: $user->email,
                subject: 'تم رفض طلب التحقق',
                body: $body
            );
        }
    }

}
