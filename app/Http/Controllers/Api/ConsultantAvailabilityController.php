<?php

namespace App\Http\Controllers\Api;

use App\DTOs\GetAvailableSlotsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetAvailableSlotsRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Consultant;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ConsultantAvailabilityController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get available time slots for a consultant on a specific date
     * GET /api/consultants/{id}/available-slots
     */
    public function availableSlots(GetAvailableSlotsRequest $request, int $consultantId): JsonResponse
    {
        try {
            // Verify consultant exists and is active
            Consultant::where('id', $consultantId)
                ->where('is_active', true)
                ->firstOrFail();

            $dto = GetAvailableSlotsDTO::fromRequest(
                $request->validated(),
                $consultantId
            );

            $slots = $this->availabilityService->getAvailableSlots(
                $dto->consultant_id,
                Carbon::parse($dto->date),
                $dto->bookable_type,
                $dto->bookable_id
            );

            return $this->resourceResponse([
                'consultant_id' => $consultantId,
                'date' => $dto->date,
                'bookable_type' => $dto->bookable_type,
                'bookable_id' => $dto->bookable_id,
                'slots' => $slots,
                'slots_count' => count($slots),
            ], 'تم جلب المواعيد المتاحة بنجاح');

        } catch (ModelNotFoundException $e) {
            $this->throwNotFoundException('المستشار غير موجود أو غير متاح');
            throw $e;
        }
    }
}
