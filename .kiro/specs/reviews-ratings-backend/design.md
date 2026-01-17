Design Document: Reviews & Ratings Backend System
Overview

This design document describes the architecture and implementation details for the Reviews & Ratings backend system. The system enables Customers (Clients) to leave reviews (rating + optional comment) for completed bookings. It follows the existing project architecture (DTO + Repository + Service pattern) and integrates with the existing Booking, Consultant, and User models.

Scope: Backend only (API + DTO/Repository/Service + Policy + Requests).
Out of Scope: Payments, frontend/UI.

Key design decisions:

One review per booking (enforced by DB unique constraint on booking_id, including soft-deleted records)

consultant_id and client_id are derived from booking (never from user input)

Only completed bookings can be reviewed

SoftDeletes for retention and moderation

Authorization is read/write restricted:

Customer: create/update/delete own review

Consultant: read-only reviews about themselves

Admin: read-only reviews (no write)

Architecture
flowchart TB
    subgraph API["API Layer"]
        RC[ReviewController]
        CRC[ConsultantReviewsController]
    end
    
    subgraph Requests["Form Requests"]
        SRR[StoreReviewRequest]
        URR[UpdateReviewRequest]
        DRR[DeleteReviewRequest]
    end
    
    subgraph Service["Service Layer"]
        RS[ReviewService]
    end
    
    subgraph Repository["Repository Layer"]
        RR[ReviewRepository]
    end
    
    subgraph Models["Model Layer"]
        RM[Review]
        BM[Booking]
        CM[Consultant]
        UM[User]
    end
    
    subgraph DTOs["Data Transfer Objects"]
        RDTO[ReviewDTO]
        CRDTO[CreateReviewDTO]
        URDTO[UpdateReviewDTO]
        GRDTO[GetReviewsDTO]
    end
    
    subgraph Policy["Authorization"]
        RP[ReviewPolicy]
    end
    
    RC --> SRR
    RC --> URR
    RC --> DRR
    RC --> RS
    CRC --> RS
    RS --> RR
    RS --> CRDTO
    RS --> URDTO
    RS --> GRDTO
    RR --> RM
    RM --> BM
    RM --> CM
    RM --> UM
    RC --> RP
    CRC --> RP
    RS --> RDTO

Data Model
Reviews Table Schema

CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    consultant_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,  -- 1..5
    comment TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    UNIQUE INDEX reviews_booking_id_unique (booking_id),

    INDEX reviews_consultant_id_created_at_index (consultant_id, created_at),
    INDEX reviews_client_id_created_at_index (client_id, created_at),

    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);


Important SoftDeletes decision:
The unique constraint on booking_id enforces exactly one review per booking even if soft-deleted. Therefore, the system must not allow creating a new review for the same booking if a soft-deleted review exists. If needed, restoration is possible via admin tooling later, but write operations remain Customer-only per policy.

Components and Interfaces
1) Review Model


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'consultant_id',
        'client_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'consultant_id' => 'integer',
        'client_id' => 'integer',
        'rating' => 'integer',
    ];

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class, 'consultant_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}

Recommended complementary relationships:

Booking::review(): HasOne

Consultant::reviews(): HasMany

User::clientReviews(): HasMany

2) ReviewRepository



<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository extends BaseRepository
{
    protected string $model = Review::class;

    // Default eager-loaded relationships
    protected array $defaultWith = [
        'client:id,first_name,last_name,avatar',
        'consultant.user:id,first_name,last_name,avatar',
        'booking:id,start_at,end_at,status',
    ];

    public function findById(int $id): ?Review
    {
        return $this->query()->with($this->defaultWith)->find($id);
    }

    public function findByBookingId(int $bookingId): ?Review
    {
        return $this->query()->with($this->defaultWith)
            ->where('booking_id', $bookingId)
            ->first();
    }

    public function bookingHasReview(int $bookingId): bool
    {
        // Includes soft-deleted reviews
        return $this->query()->withTrashed()
            ->where('booking_id', $bookingId)
            ->exists();
    }

    public function forConsultant(int $consultantId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->with($this->defaultWith)
            ->where('consultant_id', $consultantId)
            ->latest()
            ->paginate($perPage);
    }

    public function forClient(int $clientId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()->with($this->defaultWith)
            ->where('client_id', $clientId)
            ->latest()
            ->paginate($perPage);
    }
}


3) ReviewService

    <?php

namespace App\Services;

use App\DTOs\CreateReviewDTO;
use App\DTOs\UpdateReviewDTO;
use App\Models\Booking;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(
        protected ReviewRepository $reviews
    ) {}

    /**
     * Create a new review:
     * - booking must be completed
     * - booking must belong to the authenticated client
     * - one review per booking (even if soft-deleted)
     * - consultant_id and client_id derived from booking (never from request input)
     */
    public function createReview(CreateReviewDTO $dto): Review
    {
        /** @var Booking $booking */
        $booking = Booking::query()->findOrFail($dto->booking_id);

        // Ownership check
        if ((int)$booking->client_id !== (int)$dto->client_id) {
            throw ValidationException::withMessages([
                'booking' => ['booking.not_owner'],
            ]);
        }

        // Completed prerequisite
        if ($booking->status !== 'completed') {
            throw ValidationException::withMessages([
                'booking' => ['booking.not_completed'],
            ]);
        }

        // One-review-per-booking (including soft-deleted)
        if ($this->reviews->bookingHasReview($booking->id)) {
            throw ValidationException::withMessages([
                'booking' => ['booking.already_reviewed'],
            ]);
        }

        // Derive fields from booking
        $data = [
            'booking_id' => $booking->id,
            'consultant_id' => $booking->consultant_id,
            'client_id' => $booking->client_id,
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ];

        return $this->reviews->create($data);
    }

    /**
     * Update an existing review (owner only)
     */
    public function updateReview(int $reviewId, UpdateReviewDTO $dto, int $clientId): Review
    {
        $review = $this->reviews->findById($reviewId);

        if (!$review) {
            throw ValidationException::withMessages(['review' => ['review.not_found']]);
        }

        if ((int)$review->client_id !== (int)$clientId) {
            throw ValidationException::withMessages(['review' => ['review.not_owner']]);
        }

        $review->update([
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ]);

        return $review->refresh();
    }

    /**
     * Soft-delete an existing review (owner only)
     */
    public function deleteReview(int $reviewId, int $clientId): bool
    {
        $review = $this->reviews->findById($reviewId);

        if (!$review) {
            throw ValidationException::withMessages(['review' => ['review.not_found']]);
        }

        if ((int)$review->client_id !== (int)$clientId) {
            throw ValidationException::withMessages(['review' => ['review.not_owner']]);
        }

        return (bool) $review->delete();
    }

    public function find(int $id): ?Review
    {
        return $this->reviews->findById($id);
    }

    public function getConsultantReviews(int $consultantId, int $perPage = 10)
    {
        return $this->reviews->forConsultant($consultantId, $perPage);
    }

    public function getMyReviews(int $clientId, int $perPage = 10)
    {
        return $this->reviews->forClient($clientId, $perPage);
    }
}


Note: Business errors are represented here with ValidationException messages keys for simplicity. The project may have a custom exception layer; implement using existing conventions.

    4) DTOs

    // CreateReviewDTO - Input for creating a review
class CreateReviewDTO extends BaseDTO
{
    public int $booking_id;
    public int $rating;
    public ?string $comment;
    public int $client_id; // from authenticated user, never from request payload

    public static function fromRequest(array $validated, int $clientId): self;
}

// UpdateReviewDTO - Input for updating a review
class UpdateReviewDTO extends BaseDTO
{
    public int $rating;
    public ?string $comment;

    public static function fromRequest(array $validated): self;
}

// ReviewDTO - Output representation
class ReviewDTO extends BaseDTO
{
    public int $id;
    public int $booking_id;
    public int $consultant_id;
    public int $client_id;

    public string $client_name;
    public ?string $client_avatar;

    public string $consultant_name;
    public ?string $consultant_avatar;

    public int $rating;
    public ?string $comment;

    public string $booking_start_at;
    public string $booking_end_at;

    public string $created_at;
    public ?string $updated_at;

    public static function fromModel(Review $review): self;
    public function toArray(): array;
}


DTO output rules:

client_name = ${client.first_name} ${client.last_name}

same for consultant via consultant.user

5) Form Requests

    // StoreReviewRequest
class StoreReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

// UpdateReviewRequest
class UpdateReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

// DeleteReviewRequest (optional; can be plain Request)
class DeleteReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // no body required; keep for consistency if project uses it
        ];
    }
}


6) Controllers

    // ReviewController
class ReviewController extends Controller
{
    // POST /api/reviews
    public function store(StoreReviewRequest $request);

    // GET /api/reviews/{id}
    public function show(int $id);

    // PUT /api/reviews/{id}
    public function update(UpdateReviewRequest $request, int $id);

    // DELETE /api/reviews/{id}
    public function destroy(int $id);

    // GET /api/my/reviews
    public function myReviews(Request $request);
}

// ConsultantReviewsController
class ConsultantReviewsController extends Controller
{
    // GET /api/consultants/{id}/reviews
    public function index(Request $request, int $consultantId);
}


7) Authorization (ReviewPolicy)

Final authorization decision:

Only Customers (clients) can create/update/delete their reviews.

Consultants can read-only reviews about themselves.

Admins can read-only reviews.

    <?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Viewing rules:
     * - Consultant profile reviews can be public (or auth-optional).
     * - For single review view, allow:
     *   - review owner (client)
     *   - consultant who owns the booking's consultant_id
     *   - admins (read-only)
     */
    public function view(?User $user, Review $review): bool
    {
        // Public listing allowed; single view can be public too if desired.
        return true;
    }

    /**
     * Create:
     * - Customer only
     * - Booking must belong to the customer
     * - Booking must be completed
     */
    public function create(User $user, Booking $booking): bool
    {
        return (int)$booking->client_id === (int)$user->id
            && $booking->status === 'completed';
    }

    /**
     * Update:
     * - Customer only (owner)
     */
    public function update(User $user, Review $review): bool
    {
        return (int)$review->client_id === (int)$user->id;
    }

    /**
     * Delete:
     * - Customer only (owner)
     */
    public function delete(User $user, Review $review): bool
    {
        return (int)$review->client_id === (int)$user->id;
    }
}

If your project differentiates roles (customer/consultant/admin) via guards/roles/permissions, implement the “Customer only” constraint using the existing role checks in addition to ownership.

| Method | Endpoint                      | Description                                 | Auth                        |
| ------ | ----------------------------- | ------------------------------------------- | --------------------------- |
| POST   | /api/reviews                  | Create review (booking_id, rating, comment) | Customer only               |
| GET    | /api/reviews/{id}             | Show review                                 | Optional / Project-specific |
| PUT    | /api/reviews/{id}             | Update review                               | Customer only (owner)       |
| DELETE | /api/reviews/{id}             | Soft delete review                          | Customer only (owner)       |
| GET    | /api/consultants/{id}/reviews | List consultant reviews (paginated)         | Optional                    |
| GET    | /api/my/reviews               | List my reviews (paginated)                 | Customer only               |

    Pagination: Must follow existing project conventions (perPage param, meta fields, etc.)

    

    
    Correctness Properties
Property 1: One Review Per Booking Invariant (Including SoftDeletes)

For any booking, there SHALL exist at most one review record (including soft-deleted). Attempting to create a second review for the same booking SHALL result in a validation error.

Property 2: Derived Fields Consistency

For any review, consultant_id and client_id SHALL equal the corresponding fields from the associated booking. These values are derived at creation time and never accepted from user input.

Property 3: Completed Booking Prerequisite

For any review creation attempt, the associated booking's status SHALL be 'completed'. Reviews for bookings with any other status SHALL be rejected.

Property 4: Rating Range Invariant

For any review, rating SHALL be an integer in the range [1, 5]. Values outside this range SHALL be rejected.

Property 5: Ownership Authorization (Write Operations)

For any update/delete operation, the authenticated user's ID SHALL equal the review's client_id. Operations by non-owners SHALL return 403 Forbidden.

Property 6: Booking Ownership for Creation

For any review creation attempt, the authenticated user's ID SHALL equal the booking's client_id. Attempts to review another user's booking SHALL return 403 Forbidden.

    Error Handling
Validation Errors (422)

    
| Error Code          | Message (Arabic)                | Condition            |
| ------------------- | ------------------------------- | -------------------- |
| booking_id.required | معرف الحجز مطلوب                | Missing booking_id   |
| booking_id.exists   | الحجز غير موجود                 | Invalid booking_id   |
| rating.required     | التقييم مطلوب                   | Missing rating       |
| rating.min          | التقييم يجب أن يكون 1 على الأقل | Rating < 1           |
| rating.max          | التقييم يجب أن يكون 5 كحد أقصى  | Rating > 5           |
| comment.max         | التعليق يجب ألا يتجاوز 2000 حرف | Comment > 2000 chars |

Business Logic Errors (422)

    | Error Code               | Message (Arabic)            | Condition                                      |
| ------------------------ | --------------------------- | ---------------------------------------------- |
| booking.not_completed    | لا يمكن تقييم حجز غير مكتمل | Booking status != completed                    |
| booking.already_reviewed | تم تقييم هذا الحجز مسبقاً   | Review already exists (including soft-deleted) |


    Authorization Errors (403)

    | Error Code        | Message (Arabic)                 | Condition                 |
| ----------------- | -------------------------------- | ------------------------- |
| booking.not_owner | لا يمكنك تقييم حجز لا يخصك       | User != booking.client_id |
| review.not_owner  | لا يمكنك تعديل/حذف تقييم لا يخصك | User != review.client_id  |


    Not Found Errors (404)

    | Error Code        | Message (Arabic)  | Condition         |
| ----------------- | ----------------- | ----------------- |
| review.not_found  | التقييم غير موجود | Review not found  |
| booking.not_found | الحجز غير موجود   | Booking not found |


    Testing Strategy
Unit Tests

ReviewService

Create review for completed booking (success)

Reject review for non-completed booking

Reject duplicate review for the same booking (including soft-deleted)

Update review by owner

Reject update by non-owner

Soft delete review by owner

Reject delete by non-owner

ReviewRepository

Find review by booking_id

bookingHasReview includes soft-deleted

Paginate consultant reviews

Paginate client reviews

ReviewPolicy

Owner can update/delete own review

Non-owner cannot update/delete

Create allowed only for booking owner and completed booking

Integration Tests

Create → Show → Update → Delete flow

Consultant profile list endpoint pagination

My reviews endpoint pagination

Deliverables (Execution Output Requirements)

When implementing, the AI MUST:

Output a file-by-file list with correct paths

Output full file content (complete code) for all generated files

Follow existing project conventions strictly (DTO/Repository/Service/Requests/Policies/Responses)

Not implement or reference payments in any way

Expected files (minimum):

Review migration (if not already created) and Review model

ReviewRepository

ReviewService

DTOs: CreateReviewDTO, UpdateReviewDTO, ReviewDTO (+ optional list DTOs)

Form Requests: StoreReviewRequest, UpdateReviewRequest

Controllers: ReviewController, ConsultantReviewsController (or equivalent)

Routes: api.php entries

ReviewPolicy
    