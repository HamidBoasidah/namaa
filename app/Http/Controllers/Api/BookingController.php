<?php

namespace App\Http\Controllers\Api;

use App\DTOs\BookingDTO;
use App\DTOs\CreatePendingBookingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\ConfirmBookingRequest;
use App\Http\Requests\StorePendingBookingRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Consultant;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    protected BookingService $bookingService;
    protected BookingRepository $bookingRepository;

    public function __construct(BookingService $bookingService, BookingRepository $bookingRepository)
    {
        $this->middleware('auth:sanctum');
        $this->bookingService = $bookingService;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * List user's bookings (as client or consultant)
     * GET /api/bookings
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $status = $request->get('status');
        $role = $request->get('role', 'client'); // 'client' or 'consultant'

        // Build query based on role
        if ($role === 'consultant') {
            $consultant = Consultant::where('user_id', $user->id)->first();
            if (!$consultant) {
                return $this->collectionResponse(collect([]), 'لا توجد حجوزات');
            }
            $query = $this->bookingRepository->forConsultant($consultant->id);
        } else {
            $query = $this->bookingRepository->forClient($user->id);
        }

        // Filter by status if provided
        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->latest()->paginate($perPage);

        // Transform to DTOs
        $bookings->getCollection()->transform(function ($booking) {
            return BookingDTO::fromModel($booking)->toIndexArray();
        });

        return $this->collectionResponse($bookings, 'تم جلب قائمة الحجوزات بنجاح');
    }

    /**
     * View booking details
     * GET /api/bookings/{id}
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingService->find($id);

        if (!$booking) {
            $this->throwNotFoundException('الحجز غير موجود');
        }

        $this->authorize('view', $booking);

        return $this->resourceResponse(
            BookingDTO::fromModel($booking)->toArray(),
            'تم جلب بيانات الحجز بنجاح'
        );
    }

    /**
     * Create a pending booking (holds slot for 15 minutes)
     * POST /api/bookings/pending
     */
    public function storePending(StorePendingBookingRequest $request): JsonResponse
    {
        $dto = CreatePendingBookingDTO::fromRequest(
            $request->validated(),
            $request->user()->id
        );

        $booking = $this->bookingService->createPending($dto);

        return $this->createdResponse(
            BookingDTO::fromModel($booking->fresh(['client', 'consultant.user', 'bookable']))->toArray(),
            'تم إنشاء الحجز بنجاح. يرجى التأكيد خلال 15 دقيقة.'
        );
    }

    /**
     * Confirm a pending booking
     * POST /api/bookings/{id}/confirm
     */
    public function confirm(ConfirmBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findOrFail($id);
        
        $this->authorize('confirm', $booking);

        $confirmed = $this->bookingService->confirm($id, $request->user()->id);

        return $this->updatedResponse(
            BookingDTO::fromModel($confirmed->fresh(['client', 'consultant.user', 'bookable']))->toArray(),
            'تم تأكيد الحجز بنجاح'
        );
    }

    /**
     * Cancel a booking
     * POST /api/bookings/{id}/cancel
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findOrFail($id);
        
        $this->authorize('cancel', $booking);

        $cancelled = $this->bookingService->cancel(
            $id,
            $request->user(),
            $request->input('reason')
        );

        return $this->updatedResponse(
            BookingDTO::fromModel($cancelled->fresh(['client', 'consultant.user', 'bookable', 'cancelledBy']))->toArray(),
            'تم إلغاء الحجز بنجاح'
        );
    }
}
