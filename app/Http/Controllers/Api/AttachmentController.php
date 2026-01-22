<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ExceptionHandler;
use App\Models\MessageAttachment;
use App\Services\AttachmentService;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    use ExceptionHandler;

    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->middleware('auth:sanctum');
        $this->attachmentService = $attachmentService;
    }

    /**
     * Download an attachment securely
     * GET /api/attachments/{attachment}
     * 
     * @param MessageAttachment $attachment
     * @return Response
     */
    public function download(MessageAttachment $attachment): Response
    {
        // Authorize: user must be participant in conversation containing the message
        $this->authorize('download', $attachment);

        // Delegate to AttachmentService to download attachment
        // Service handles file retrieval and response headers
        return $this->attachmentService->downloadAttachment($attachment);
    }
}
