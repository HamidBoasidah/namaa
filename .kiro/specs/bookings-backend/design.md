# Design Document: Bookings Backend System

## Overview

This design document describes the architecture and implementation details for a robust Bookings backend system. The system follows the existing project's DTO + Repository + Service architecture pattern and integrates with existing Consultant, ConsultantService, ConsultantWorkingHour, and ConsultantHoliday models.

The booking system supports two booking types:
1. **Direct Consultant Booking** (hourly) - Client provides duration
2. **ConsultantService Booking** (fixed) - Duration from service definition

Key features include:
- Polymorphic bookable support (Consultant or ConsultantService)
- 5-minute time granularity enforcement
- Availability validation (working hours, holidays, conflicts)
- Race condition prevention with pessimistic locking
- 15-minute pending hold with automatic expiration
- Polymorphic canceller tracking

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Layer                                 │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │ BookingController│  │AvailabilityCtrl │  │  Form Requests  │  │
│  └────────┬────────┘  └────────┬────────┘  └─────────────────┘  │
└───────────┼────────────────────┼────────────────────────────────┘
            │                    │
┌───────────┼────────────────────┼────────────────────────────────┐
│           ▼                    ▼         Service Layer           │
│  ┌─────────────────┐  ┌─────────────────┐                       │
│  │ BookingService  │  │AvailabilityServ │                       │
│  │                 │  │                 │                       │
│  │ - createPending │  │ - getAvailable  │                       │
│  │ - confirm       │  │   Slots         │                       │
│  │ - cancel        │  │ - validateSlot  │                       │
│  │ - expireOld     │  │                 │                       │
│  └────────┬────────┘  └────────┬────────┘                       │
└───────────┼────────────────────┼────────────────────────────────┘
            │                    │
┌───────────┼────────────────────┼────────────────────────────────┐
│           ▼                    ▼       Repository Layer          │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    BookingRepository                         ││
│  │                                                              ││
│  │ - createPending()      - findBlockingOverlaps()             ││
│  │ - confirm()            - expirePending()                    ││
│  │ - cancel()             - findById()                         ││
│  └──────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
            │
┌───────────┼─────────────────────────────────────────────────────┐
│           ▼              Data Layer                              │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  Booking Model  │  │   Consultant    │  │ConsultantService│  │
│  │                 │  │   WorkingHour   │  │                 │  │
│  │                 │  │   Holiday       │  │                 │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Booking Model

```php
// app/Models/Booking.php
class Booking extends BaseModel
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    // Blocking statuses for conflict checks
    const BLOCKING_STATUSES = [self::STATUS_CONFIRMED, self::STATUS_PENDING];

    protected $fillable = [
        'client_id',
        'consultant_id',
        'bookable_type',
        'bookable_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'buffer_after_minutes',
        'status',
        'expires_at',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by_type',
        'cancelled_by_id',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'buffer_after_minutes' => 'integer',
    ];

    // Relationships
    public function client(): BelongsTo;           // User
    public function consultant(): BelongsTo;       // Consultant
    public function bookable(): MorphTo;           // Consultant|ConsultantService
    public function cancelledBy(): MorphTo;        // User|Admin

    // Scopes
    public function scopeBlocking($query);         // confirmed OR (pending AND expires_at > now)
    public function scopeForConsultant($query, int $consultantId);
    public function scopeOverlapping($query, Carbon $start, Carbon $end);
}
```

### 2. BookingRepository

```php
// app/Repositories/BookingRepository.php
class BookingRepository extends BaseRepository
{
    /**
     * Create a pending booking (called within transaction)
     */
    public function createPending(array $data): Booking;

    /**
     * Find blocking bookings that overlap with given time range
     * Blocking = confirmed OR (pending AND expires_at > now)
     */
    public function findBlockingOverlaps(
        int $consultantId,
        Carbon $occupiedStart,
        Carbon $occupiedEnd,
        ?int $excludeBookingId = null
    ): Collection;

    /**
     * Confirm a pending booking
     */
    public function confirm(Booking $booking): Booking;

    /**
     * Expire all pending bookings where expires_at <= now
     */
    public function expirePending(): int;

    /**
     * Cancel a booking with canceller info
     */
    public function cancel(
        Booking $booking,
        Model $cancelledBy,
        ?string $reason = null
    ): Booking;

    /**
     * Get bookings for a consultant on a specific date
     */
    public function getBlockingForDate(int $consultantId, Carbon $date): Collection;
}
```

### 3. AvailabilityService

```php
// app/Services/AvailabilityService.php
class AvailabilityService
{
    /**
     * Get available time slots for a consultant on a specific date
     * 
     * @param int $consultantId
     * @param Carbon $date
     * @param string|null $bookableType  'consultant' or 'consultant_service'
     * @param int|null $bookableId
     * @param int $granularity  Time slot granularity in minutes (default 5)
     * @return array  Array of available slot start times
     */
    public function getAvailableSlots(
        int $consultantId,
        Carbon $date,
        ?string $bookableType = null,
        ?int $bookableId = null,
        int $granularity = 5
    ): array;

    /**
     * Validate if a specific slot is available
     * 
     * @return array ['valid' => bool, 'reason' => string|null]
     */
    public function validateSlot(
        int $consultantId,
        Carbon $startAt,
        int $durationMinutes,
        int $bufferAfterMinutes,
        ?int $excludeBookingId = null
    ): array;

    /**
     * Check if date is a holiday for consultant
     */
    public function isHoliday(int $consultantId, Carbon $date): bool;

    /**
     * Get working hours for consultant on specific weekday
     */
    public function getWorkingHoursForDay(int $consultantId, int $dayOfWeek): Collection;

    /**
     * Check if time range fits within any working hour slot
     */
    public function fitsInWorkingHours(
        int $consultantId,
        Carbon $startAt,
        Carbon $endAt
    ): bool;
}
```

### 4. BookingService

```php
// app/Services/BookingService.php
class BookingService
{
    /**
     * Create a pending booking with slot hold
     * Uses transaction + pessimistic locking to prevent race conditions
     * 
     * @throws ValidationException if slot unavailable
     * @throws BusinessLogicException if consultant/service not found
     */
    public function createPending(CreatePendingBookingDTO $dto): Booking;

    /**
     * Confirm a pending booking
     * Uses transaction + pessimistic locking
     * Re-checks availability before confirming
     * 
     * @throws ValidationException if booking expired or conflicts detected
     * @throws NotFoundException if booking not found
     */
    public function confirm(int $bookingId, int $clientId): Booking;

    /**
     * Cancel a booking
     * Records who cancelled (polymorphic)
     */
    public function cancel(
        int $bookingId,
        Model $cancelledBy,
        ?string $reason = null
    ): Booking;

    /**
     * Expire all old pending bookings
     * Called by scheduled job
     */
    public function expireOldPending(): int;

    /**
     * Resolve duration and buffer based on bookable type
     */
    protected function resolveDurationAndBuffer(
        string $bookableType,
        int $bookableId,
        ?int $userDuration,
        Consultant $consultant
    ): array;
}
```

### 5. DTOs

```php
// app/DTOs/CreatePendingBookingDTO.php
class CreatePendingBookingDTO extends BaseDTO
{
    public int $client_id;
    public int $consultant_id;
    public string $bookable_type;      // 'consultant' or 'consultant_service'
    public int $bookable_id;
    public string $start_at;           // ISO datetime string
    public ?int $duration_minutes;     // Required for consultant, ignored for service
    public ?string $notes;

    public static function fromRequest(array $data, int $clientId): self;
}

// app/DTOs/ConfirmBookingDTO.php
class ConfirmBookingDTO extends BaseDTO
{
    public int $booking_id;
    public int $client_id;

    public static function fromRequest(int $bookingId, int $clientId): self;
}

// app/DTOs/GetAvailableSlotsDTO.php
class GetAvailableSlotsDTO extends BaseDTO
{
    public int $consultant_id;
    public string $date;               // Y-m-d format
    public ?string $bookable_type;
    public ?int $bookable_id;

    public static function fromRequest(array $data, int $consultantId): self;
}

// app/DTOs/BookingDTO.php
class BookingDTO extends BaseDTO
{
    // All booking fields for API responses
    public static function fromModel(Booking $booking): self;
    public function toArray(): array;
}
```

### 6. Form Requests

```php
// app/Http/Requests/StorePendingBookingRequest.php
class StorePendingBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'consultant_id' => 'required|exists:consultants,id',
            'bookable_type' => 'required|in:consultant,consultant_service',
            'bookable_id' => 'required|integer',
            'start_at' => [
                'required',
                'date',
                'after:now',
                // Custom rule: must be multiple of 5 minutes
            ],
            'duration_minutes' => [
                'required_if:bookable_type,consultant',
                'nullable',
                'integer',
                'min:5',
                'max:480',
                // Custom rule: must be multiple of 5
            ],
            'notes' => 'nullable|string|max:1000',
        ];
    }
}

// app/Http/Requests/ConfirmBookingRequest.php
class ConfirmBookingRequest extends FormRequest
{
    // Minimal - just validates booking exists and belongs to user
}

// app/Http/Requests/CancelBookingRequest.php
class CancelBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:500',
        ];
    }
}

// app/Http/Requests/GetAvailableSlotsRequest.php
class GetAvailableSlotsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'bookable_type' => 'nullable|in:consultant,consultant_service',
            'bookable_id' => 'required_with:bookable_type|integer',
        ];
    }
}
```

### 7. Controllers

```php
// app/Http/Controllers/Api/BookingController.php
class BookingController extends Controller
{
    /**
     * POST /api/bookings/pending
     * Create a pending booking (holds slot for 15 minutes)
     */
    public function storePending(StorePendingBookingRequest $request): JsonResponse;

    /**
     * POST /api/bookings/{id}/confirm
     * Confirm a pending booking
     */
    public function confirm(ConfirmBookingRequest $request, int $id): JsonResponse;

    /**
     * POST /api/bookings/{id}/cancel
     * Cancel a booking
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse;

    /**
     * GET /api/bookings
     * List user's bookings (client or consultant)
     */
    public function index(Request $request): JsonResponse;

    /**
     * GET /api/bookings/{id}
     * View booking details
     */
    public function show(Request $request, int $id): JsonResponse;
}

// app/Http/Controllers/Api/ConsultantAvailabilityController.php
class ConsultantAvailabilityController extends Controller
{
    /**
     * GET /api/consultants/{id}/available-slots
     * Get available time slots for a consultant
     */
    public function availableSlots(
        GetAvailableSlotsRequest $request,
        int $consultantId
    ): JsonResponse;
}
```

### 8. Policy

```php
// app/Policies/BookingPolicy.php
class BookingPolicy
{
    /**
     * Client can view their own bookings
     * Consultant can view bookings for their consultations
     * Admin can view any booking
     */
    public function view(User $user, Booking $booking): bool;

    /**
     * Only authenticated users can create bookings
     */
    public function create(User $user): bool;

    /**
     * Client can cancel their own bookings
     * Consultant can cancel their consultation bookings
     * Admin can cancel any booking
     */
    public function cancel(User $user, Booking $booking): bool;
}
```

### 9. Scheduled Job

```php
// app/Console/Commands/ExpirePendingBookingsCommand.php
class ExpirePendingBookingsCommand extends Command
{
    protected $signature = 'bookings:expire-pending';
    protected $description = 'Mark expired pending bookings as expired';

    public function handle(BookingService $service): int;
}

// Scheduled in app/Console/Kernel.php or routes/console.php
// $schedule->command('bookings:expire-pending')->everyMinute();
```

## Data Models

### Booking Table Schema

```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Parties
    client_id BIGINT UNSIGNED NOT NULL,
    consultant_id BIGINT UNSIGNED NOT NULL,
    
    -- Polymorphic bookable (Consultant or ConsultantService)
    bookable_type VARCHAR(255) NOT NULL,
    bookable_id BIGINT UNSIGNED NOT NULL,
    
    -- Time
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    buffer_after_minutes SMALLINT UNSIGNED DEFAULT 0,
    
    -- Status
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'expired') DEFAULT 'pending',
    expires_at DATETIME NULL,
    
    -- Cancellation (polymorphic canceller)
    cancelled_at DATETIME NULL,
    cancel_reason VARCHAR(500) NULL,
    cancelled_by_type VARCHAR(255) NULL,
    cancelled_by_id BIGINT UNSIGNED NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE CASCADE,
    
    -- Indexes for availability queries
    INDEX idx_consultant_start (consultant_id, start_at),
    INDEX idx_consultant_end (consultant_id, end_at),
    INDEX idx_consultant_status (consultant_id, status),
    INDEX idx_status_expires (status, expires_at),
    INDEX idx_client_status (client_id, status),
    INDEX idx_bookable (bookable_type, bookable_id)
);
```

### Occupied Window Calculation

```
occupied_start = booking.start_at
occupied_end = booking.end_at + booking.buffer_after_minutes

Example:
- Booking: 10:00 - 11:00 (60 min)
- Buffer: 15 min
- Occupied Window: 10:00 - 11:15
- Next available slot: 11:15 or later
```

### Blocking Booking Logic

```php
// A booking is blocking if:
// 1. status = 'confirmed', OR
// 2. status = 'pending' AND expires_at > NOW()

$blocking = Booking::where('consultant_id', $consultantId)
    ->where(function ($q) {
        $q->where('status', 'confirmed')
          ->orWhere(function ($q2) {
              $q2->where('status', 'pending')
                 ->where('expires_at', '>', now());
          });
    });
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Time Granularity Validation

*For any* booking creation request, the system SHALL accept start_at only if its minutes component is divisible by 5, and SHALL accept duration_minutes only if it is divisible by 5. All other values SHALL be rejected with validation errors.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**

### Property 2: Booking Type Duration Resolution

*For any* booking with bookable_type='consultant_service', the stored duration_minutes SHALL equal the ConsultantService.duration_minutes regardless of any user-provided duration. *For any* booking with bookable_type='consultant', the stored duration_minutes SHALL equal the user-provided duration_minutes.

**Validates: Requirements 3.1, 3.2**

### Property 3: Buffer Resolution and Snapshot

*For any* booking, the buffer_after_minutes SHALL be captured at creation time from: (a) ConsultantService.buffer if bookable is ConsultantService and buffer is set, otherwise (b) Consultant.buffer. Subsequent changes to the source buffer SHALL NOT affect the booking's buffer_after_minutes.

**Validates: Requirements 3.3, 3.4, 3.5**

### Property 4: Working Hours Validation

*For any* booking creation, the booking SHALL be accepted only if there exists at least one active working hour slot for the consultant on the booking's weekday where: working_hour.start_time <= booking.start_at.time AND booking.end_at.time + buffer <= working_hour.end_time.

**Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.5**

### Property 5: Holiday Validation

*For any* booking creation where the booking date matches a ConsultantHoliday.holiday_date for the consultant, the booking SHALL be rejected regardless of the time component.

**Validates: Requirements 5.1, 5.2, 5.3**

### Property 6: Overlap Detection with Blocking Status

*For any* new booking creation, the system SHALL reject if there exists any booking B where:
- B.consultant_id = new.consultant_id
- B.status = 'confirmed' OR (B.status = 'pending' AND B.expires_at > now)
- Overlap exists: new.start_at < B.end_at + B.buffer_after_minutes AND new.end_at + new.buffer_after_minutes > B.start_at

Bookings with status in {cancelled, expired, completed} SHALL NOT block new bookings.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

### Property 7: Pending Booking Lifecycle

*For any* newly created booking, status SHALL be 'pending' and expires_at SHALL be set to creation_time + 15 minutes. *For any* pending booking where expires_at <= now, the booking SHALL NOT be considered blocking for conflict checks.

**Validates: Requirements 8.1, 8.2, 8.3**

### Property 8: Expiration Job Correctness

*For any* execution of the expiration job, all bookings where status='pending' AND expires_at <= now SHALL have their status updated to 'expired'. No other bookings SHALL be modified.

**Validates: Requirements 11.2, 11.3**

### Property 9: Confirmation State Transitions

*For any* booking confirmation attempt:
- If booking.status != 'pending', confirmation SHALL fail
- If booking.expires_at <= now, confirmation SHALL fail
- If conflicts exist (per Property 6), confirmation SHALL fail
- Otherwise, booking.status SHALL become 'confirmed' and expires_at SHALL be cleared

**Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5, 9.6**

### Property 10: Cancellation with Polymorphic Canceller

*For any* successful cancellation, the booking SHALL have:
- status = 'cancelled'
- cancelled_at = current timestamp
- cancelled_by_type = canceller's model class
- cancelled_by_id = canceller's id

The cancelled booking SHALL NOT be considered blocking for future bookings.

**Validates: Requirements 10.1, 10.2, 10.3, 10.5, 10.6**

### Property 11: Available Slots Calculation

*For any* available slots query for consultant C on date D:
- If D is a holiday for C, return empty array
- Otherwise, *for all* returned slots S:
  - S.time is divisible by 5 minutes
  - S falls within an active working hour for C on D's weekday
  - No blocking booking exists that would overlap with [S, S + duration + buffer]

**Validates: Requirements 12.2, 12.3, 12.4, 12.5, 12.6**

### Property 12: Authorization Invariants

*For any* booking B:
- Client with id = B.client_id CAN view and cancel B
- Consultant whose consultant.id = B.consultant_id CAN view and cancel B
- Admin CAN view and cancel any booking
- Other users CANNOT view or cancel B

**Validates: Requirements 13.1, 13.2, 13.3, 13.4, 13.5, 13.6**

## Error Handling

### Validation Errors (422)

| Error Code | Message | Condition |
|------------|---------|-----------|
| `invalid_time_granularity` | وقت البداية يجب أن يكون من مضاعفات 5 دقائق | start_at minutes not divisible by 5 |
| `invalid_duration_granularity` | المدة يجب أن تكون من مضاعفات 5 دقائق | duration_minutes not divisible by 5 |
| `duration_required` | المدة مطلوبة للحجز المباشر مع المستشار | bookable_type=consultant without duration |
| `invalid_bookable` | الخدمة أو المستشار غير موجود | bookable not found |
| `consultant_mismatch` | الخدمة لا تنتمي لهذا المستشار | service.consultant_id != consultant_id |

### Business Logic Errors (409/422)

| Error Code | Message | Condition |
|------------|---------|-----------|
| `outside_working_hours` | الوقت المحدد خارج ساعات عمل المستشار | Booking outside working hours |
| `holiday_conflict` | المستشار في إجازة في هذا التاريخ | Booking on consultant holiday |
| `slot_unavailable` | الموعد المحدد غير متاح | Overlapping blocking booking exists |
| `booking_expired` | انتهت صلاحية الحجز | Confirming expired pending booking |
| `invalid_status` | لا يمكن تأكيد هذا الحجز | Confirming non-pending booking |
| `cannot_cancel` | لا يمكن إلغاء هذا الحجز | Cancelling completed/expired booking |

### Authorization Errors (403)

| Error Code | Message | Condition |
|------------|---------|-----------|
| `unauthorized_view` | غير مصرح لك بعرض هذا الحجز | User not authorized to view booking |
| `unauthorized_cancel` | غير مصرح لك بإلغاء هذا الحجز | User not authorized to cancel booking |

## Testing Strategy

### Unit Tests

Unit tests will cover specific examples and edge cases:

1. **Model Tests**
   - Booking relationships resolve correctly
   - Scopes filter correctly (blocking, forConsultant, overlapping)
   - Status constants are correct

2. **Validation Tests**
   - Time granularity validation (valid/invalid times)
   - Duration validation for different bookable types
   - Required fields validation

3. **Service Edge Cases**
   - Booking at exact working hour boundaries
   - Booking with zero buffer
   - Multiple working hour slots on same day

### Property-Based Tests

Property-based tests will use **Pest PHP** with **pest-plugin-faker** for generating random test data. Each property test will run minimum 100 iterations.

```php
// Example property test structure
it('validates time granularity for all inputs', function () {
    // Generate random datetime
    // Assert: accepted if minutes % 5 == 0, rejected otherwise
})->repeat(100);
```

**Test Configuration:**
- Framework: Pest PHP
- Property testing: Custom generators with Faker
- Minimum iterations: 100 per property
- Tag format: `@property Feature: bookings-backend, Property N: description`

### Integration Tests

1. **Full booking flow**: pending → confirm → complete
2. **Cancellation flow**: pending/confirmed → cancelled
3. **Expiration flow**: pending → expired (via job)
4. **Concurrent booking attempts** (race condition prevention)
5. **Available slots with various booking configurations**
