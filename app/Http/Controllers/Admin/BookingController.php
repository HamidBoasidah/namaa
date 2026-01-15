<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BookingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;

class BookingController extends Controller
{
	public function __construct()
	{
		$this->middleware('permission:bookings.view')->only(['index', 'show']);
		$this->middleware('permission:bookings.create')->only(['create', 'store']);
		$this->middleware('permission:bookings.update')->only(['edit', 'update', 'cancel']);
		$this->middleware('permission:bookings.delete')->only(['destroy']);
	}

	public function index(Request $request, BookingService $bookingService)
	{
		$perPage = (int) $request->input('per_page', 10);

		$bookings = $bookingService->paginate($perPage, []);

		$bookings->getCollection()->transform(fn ($booking) => BookingDTO::fromModel($booking)->toIndexArray());

		// Get status counts for ALL bookings (not just current page)
		$statusCounts = Booking::selectRaw('status, COUNT(*) as count')
			->groupBy('status')
			->pluck('count', 'status')
			->toArray();

		// Ensure all statuses have a value
		$allStatusCounts = [
			'pending' => $statusCounts['pending'] ?? 0,
			'confirmed' => $statusCounts['confirmed'] ?? 0,
			'completed' => $statusCounts['completed'] ?? 0,
			'cancelled' => $statusCounts['cancelled'] ?? 0,
			'expired' => $statusCounts['expired'] ?? 0,
		];

		return Inertia::render('Admin/Booking/Index', [
			'bookings' => $bookings,
			'filters' => [],
			'statusCounts' => $allStatusCounts,
		]);
	}

	public function create()
	{
		[$consultants, $clients, $services] = $this->formLookups();

		return Inertia::render('Admin/Booking/Create', [
			'consultants' => $consultants,
			'clients' => $clients,
			'services' => $services,
		]);
	}

	public function store(StoreBookingRequest $request, BookingService $bookingService)
	{
		$data = $request->validated();

		// Normalize bookable_type to model class
		$bookableType = $data['bookable_type'] === 'consultant'
			? Consultant::class
			: ConsultantService::class;

		// Ensure bookable_id matches consultant for direct booking
		if ($data['bookable_type'] === 'consultant') {
			$data['bookable_id'] = $data['consultant_id'];
		}

		$startAt = Carbon::parse($data['start_at']);
		$endAt = $startAt->copy()->addMinutes($data['duration_minutes']);

		$bookingService->create([
			'client_id' => $data['client_id'],
			'consultant_id' => $data['consultant_id'],
			'bookable_type' => $bookableType,
			'bookable_id' => $data['bookable_id'],
			'start_at' => $startAt,
			'end_at' => $endAt,
			'duration_minutes' => $data['duration_minutes'],
			'buffer_after_minutes' => $data['buffer_after_minutes'] ?? 0,
			'status' => $data['status'] ?? Booking::STATUS_CONFIRMED,
			'notes' => $data['notes'] ?? null,
		]);

		return Redirect::route('admin.bookings.index');
	}

	public function show(Booking $booking)
	{
		$booking->load([
			// include email and phone_number so DTO can expose contact info
			'client:id,first_name,last_name,avatar,email,phone_number',
			'consultant.user:id,first_name,last_name,avatar,email,phone_number',
			'bookable',
			'cancelledBy',
		]);

		$dto = BookingDTO::fromModel($booking)->toArray();

		return Inertia::render('Admin/Booking/Show', [
			'booking' => $dto,
		]);
	}

	public function edit(Booking $booking)
	{
		$booking->load([
			// include email and phone_number so DTO can expose contact info
			'client:id,first_name,last_name,avatar,email,phone_number',
			'consultant.user:id,first_name,last_name,avatar,email,phone_number',
			'bookable',
		]);

		[$consultants, $clients, $services] = $this->formLookups();

		$dto = BookingDTO::fromModel($booking)->toArray();

		return Inertia::render('Admin/Booking/Edit', [
			'booking' => $dto,
			'consultants' => $consultants,
			'clients' => $clients,
			'services' => $services,
		]);
	}

	public function update(UpdateBookingRequest $request, BookingService $bookingService, Booking $booking)
	{
		$data = $request->validated();

		$startAt = Carbon::parse($data['start_at']);
		$endAt = $startAt->copy()->addMinutes($data['duration_minutes']);

		$bookingService->update($booking->id, [
			'start_at' => $startAt,
			'end_at' => $endAt,
			'duration_minutes' => $data['duration_minutes'],
			'buffer_after_minutes' => $data['buffer_after_minutes'] ?? 0,
			'status' => $data['status'] ?? $booking->status,
			'notes' => $data['notes'] ?? null,
		]);

		return Redirect::route('admin.bookings.index');
	}

	public function destroy(BookingService $bookingService, Booking $booking)
	{
		$bookingService->delete($booking->id);

		return Redirect::route('admin.bookings.index');
	}

	public function cancel(BookingService $bookingService, Booking $booking)
	{
		$bookingService->cancel($booking->id, request()->user());

		return Redirect::back()->with('success', __('bookings.cancelledSuccessfully'));
	}

	/**
	 * Get available time slots for a consultant on a specific date
	 * Returns slots with availability status for UI to disable unavailable slots
	 */
	public function availableSlots(Request $request)
	{
		$request->validate([
			'consultant_id' => 'required|exists:consultants,id',
			'date' => 'required|date',
			'duration' => 'nullable|integer|min:5',
			'bookable_type' => 'nullable|string|in:consultant,consultant_service',
			'bookable_id' => 'nullable|integer',
		]);

		$consultantId = $request->input('consultant_id');
		$date = Carbon::parse($request->input('date'));
		$duration = (int) $request->input('duration', 60);
		$bookableType = $request->input('bookable_type', 'consultant');
		$bookableId = $request->input('bookable_id');

		// Get consultant with buffer
		$consultant = Consultant::find($consultantId);
		if (!$consultant) {
			return response()->json(['slots' => []]);
		}

		// Determine buffer based on bookable type
		$buffer = (int) ($consultant->buffer ?? 0);
		if ($bookableType === 'consultant_service' && $bookableId) {
			$service = ConsultantService::find($bookableId);
			if ($service) {
				$duration = (int) $service->duration_minutes;
				$buffer = (int) ($service->buffer ?? $consultant->buffer ?? 0);
			}
		}

		// Get consultant's working hours for this day
		$dayOfWeek = $date->dayOfWeek;
		$workingHours = \App\Models\ConsultantWorkingHour::where('consultant_id', $consultantId)
			->where('day_of_week', $dayOfWeek)
			->where('is_active', true)
			->get();

		// Get consultant's holidays
		$isHoliday = \App\Models\ConsultantHoliday::where('consultant_id', $consultantId)
			->whereDate('holiday_date', $date)
			->exists();

		if ($isHoliday || $workingHours->isEmpty()) {
			return response()->json(['slots' => []]);
		}

		// Get existing bookings for this consultant on this date (blocking only)
		// Blocking = confirmed OR (pending with expires_at > now)
		$existingBookings = Booking::where('consultant_id', $consultantId)
			->whereDate('start_at', $date)
			->where(function ($query) {
				$query->where('status', Booking::STATUS_CONFIRMED)
					->orWhere(function ($q) {
						$q->where('status', Booking::STATUS_PENDING)
						  ->where('expires_at', '>', now());
					});
			})
			->get();

		$slots = [];

		foreach ($workingHours as $wh) {
			$startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->start_time);
			$endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $wh->end_time);

			// Generate slots every 30 minutes
			$current = $startTime->copy();
			while ($current->copy()->addMinutes($duration + $buffer)->lte($endTime)) {
				$slotEnd = $current->copy()->addMinutes($duration);
				$slotOccupiedEnd = $slotEnd->copy()->addMinutes($buffer);
				
				// Check if slot is in the past first
				$isAvailable = true;
				$conflictReason = null;
				
				if ($current->lt(now())) {
					$isAvailable = false;
					$conflictReason = 'past';
				} else {
					// Check if slot is available (not overlapping with existing bookings)
					foreach ($existingBookings as $booking) {
						$bookingStart = $booking->start_at;
						$bookingOccupiedEnd = $booking->end_at->copy()->addMinutes((int) ($booking->buffer_after_minutes ?? 0));
						
						// Check for overlap: new_start < existing_occupied_end AND new_occupied_end > existing_start
						if ($current->lt($bookingOccupiedEnd) && $slotOccupiedEnd->gt($bookingStart)) {
							$isAvailable = false;
							$conflictReason = 'booked';
							break;
						}
					}
				}

				$hour = $current->hour;
				$minute = $current->format('i');
				$displayHour = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
				$period = $hour >= 12 ? 'م' : 'ص';

				$slots[] = [
					'time' => $current->format('H:i'),
					'display' => $displayHour . ':' . $minute . ' ' . $period,
					'available' => $isAvailable,
					'reason' => $conflictReason,
				];

				$current->addMinutes(30);
			}
		}

		return response()->json(['slots' => $slots]);
	}

	/**
	 * Build dropdown lookups for create/edit forms
	 */
	protected function formLookups(): array
	{
		$consultants = Consultant::with('user:id,first_name,last_name')
			->select('id', 'user_id', 'buffer')
			->get()
			->map(fn ($c) => [
				'id' => $c->id,
				'name' => $c->user ? trim("{$c->user->first_name} {$c->user->last_name}") : "#{$c->id}",
				'buffer' => $c->buffer ?? 0,
			])
			->sortBy('name')
			->values();

		$clients = User::select('id', 'first_name', 'last_name', 'avatar')
			->when(Schema::hasColumn('users', 'user_type'), function ($q) {
				$q->where('user_type', 'customer');
			})
			->orderBy('first_name')
			->get()
			->map(fn ($u) => [
				'id' => $u->id,
				'name' => trim("{$u->first_name} {$u->last_name}"),
			]);

		$services = ConsultantService::select('id', 'title', 'duration_minutes', 'buffer', 'consultant_id')
			->where('is_active', true)
			->get();

		return [$consultants, $clients, $services];
	}
}
