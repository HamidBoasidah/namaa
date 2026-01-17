# Implementation Plan: Reviews & Ratings Backend System

## Overview

This implementation plan breaks down the Reviews & Ratings backend system into discrete coding tasks following the existing project architecture (DTO + Repository + Service pattern). Each task builds incrementally on previous work, ensuring no orphaned code.

## Tasks

- [ ] 1. Set up Review Model and update related models
  - [x] 1.1 Update Review model with fillable, casts, SoftDeletes, and relationships
    - Extend BaseModel, use HasFactory and SoftDeletes traits
    - Define fillable: booking_id, consultant_id, client_id, rating, comment
    - Define casts for integer fields
    - Add belongsTo relationships: booking(), consultant(), client()
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 9.1, 9.2, 9.3_

  - [-] 1.2 Add Review relationship to Booking model
    - Add hasOne relationship: review()
    - _Requirements: 9.4_

  - [ ] 1.3 Add Reviews relationship to Consultant model
    - Add hasMany relationship: reviews()
    - _Requirements: 9.5_

  - [ ] 1.4 Add Reviews relationship to User model
    - Add hasMany relationship: clientReviews()
    - _Requirements: 9.6_

- [ ] 2. Implement ReviewRepository
  - [ ] 2.1 Create ReviewRepository class
    - Extend BaseRepository
    - Set defaultWith for eager loading (client, consultant.user, booking)
    - Implement findByBookingId(int $bookingId): ?Review
    - Implement bookingHasReview(int $bookingId): bool (including soft-deleted)
    - Implement forConsultant(int $consultantId, int $perPage): LengthAwarePaginator
    - Implement forClient(int $clientId, int $perPage): LengthAwarePaginator
    - _Requirements: 7.1_

  - [ ] 2.2 Register ReviewRepository in RepositoryServiceProvider
    - Add binding in register() method
    - _Requirements: 7.1_

- [ ] 3. Implement DTOs
  - [ ] 3.1 Create CreateReviewDTO
    - Properties: booking_id, rating, comment, client_id
    - Static fromRequest(array $validated, int $clientId): self
    - _Requirements: 7.3_

  - [ ] 3.2 Create UpdateReviewDTO
    - Properties: rating, comment
    - Static fromRequest(array $validated): self
    - _Requirements: 7.3_

  - [ ] 3.3 Create ReviewDTO
    - Properties: id, booking_id, consultant_id, client_id, client_name, client_avatar, consultant_name, consultant_avatar, rating, comment, booking_start_at, booking_end_at, created_at, updated_at
    - Static fromModel(Review $review): self
    - toArray(): array method
    - _Requirements: 7.3_

- [ ] 4. Implement ReviewService
  - [ ] 4.1 Create ReviewService class
    - Inject ReviewRepository in constructor
    - Implement createReview(CreateReviewDTO $dto): Review
      - Validate booking exists and belongs to client
      - Validate booking status is 'completed'
      - Check no existing review (including soft-deleted)
      - Derive consultant_id and client_id from booking
    - Implement updateReview(int $reviewId, UpdateReviewDTO $dto, int $clientId): Review
      - Validate review exists and belongs to client
    - Implement deleteReview(int $reviewId, int $clientId): bool
      - Validate review exists and belongs to client
      - Soft delete
    - Implement find(int $id): ?Review
    - Implement getConsultantReviews(int $consultantId, int $perPage): LengthAwarePaginator
    - Implement getMyReviews(int $clientId, int $perPage): LengthAwarePaginator
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 7.2_

  - [ ] 4.2 Write property test for One Review Per Booking Invariant
    - **Property 1: One Review Per Booking Invariant**
    - Generate random completed bookings
    - Create first review (should succeed)
    - Attempt second review (should fail)
    - Verify soft-deleted reviews also block new reviews
    - **Validates: Requirements 3.3, 3.4**

  - [ ] 4.3 Write property test for Data Consistency
    - **Property 2: Derived Fields Consistency**
    - Generate random bookings with various consultant_id/client_id
    - Create reviews
    - Assert review.consultant_id === booking.consultant_id
    - Assert review.client_id === booking.client_id
    - **Validates: Requirements 2.1, 2.2, 2.4**

  - [ ] 4.4 Write property test for Completed Booking Prerequisite
    - **Property 3: Completed Booking Prerequisite**
    - Generate bookings with random statuses
    - Attempt to create reviews
    - Assert only completed bookings allow review creation
    - **Validates: Requirements 3.1, 3.2**

  - [ ] 4.5 Write property test for Rating Range Invariant
    - **Property 4: Rating Range Invariant**
    - Generate random integers (including out-of-range)
    - Attempt to create reviews with various ratings
    - Assert only ratings 1-5 are accepted
    - **Validates: Requirements 4.1, 4.2**

- [ ] 5. Checkpoint - Ensure core logic tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Implement Form Requests
  - [ ] 6.1 Create StoreReviewRequest
    - Rules: booking_id (required, integer, exists:bookings,id), rating (required, integer, min:1, max:5), comment (nullable, string, max:2000)
    - Arabic validation messages
    - _Requirements: 4.1, 4.2, 4.3, 7.4, 8.1_

  - [ ] 6.2 Create UpdateReviewRequest
    - Rules: rating (required, integer, min:1, max:5), comment (nullable, string, max:2000)
    - Arabic validation messages
    - _Requirements: 4.1, 4.2, 4.3, 7.4, 8.2_

- [ ] 7. Implement ReviewPolicy
  - [ ] 7.1 Create ReviewPolicy class
    - Implement view(?User $user, Review $review): bool - allow public viewing
    - Implement create(User $user, Booking $booking): bool - booking owner + completed status
    - Implement update(User $user, Review $review): bool - review owner only
    - Implement delete(User $user, Review $review): bool - review owner only
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ] 7.2 Register ReviewPolicy in AuthServiceProvider
    - Add policy mapping for Review model
    - _Requirements: 6.1_

  - [ ] 7.3 Write property test for Ownership Authorization
    - **Property 5: Ownership Authorization**
    - Generate reviews with random client_ids
    - Attempt update/delete with different user contexts
    - Assert only owner can modify
    - **Validates: Requirements 6.2, 6.3, 6.6**

  - [ ] 7.4 Write property test for Booking Ownership for Creation
    - **Property 6: Booking Ownership for Creation**
    - Generate bookings with random client_ids
    - Attempt to create reviews with different authenticated users
    - Assert only booking owner can create review
    - **Validates: Requirements 2.3, 6.1**

- [ ] 8. Implement Controllers
  - [ ] 8.1 Create ReviewController
    - Inject ReviewService and ReviewRepository
    - Use ExceptionHandler and SuccessResponse traits
    - Implement store(StoreReviewRequest $request): JsonResponse
      - Create CreateReviewDTO from request
      - Call service createReview
      - Return createdResponse with ReviewDTO
    - Implement show(int $id): JsonResponse
      - Find review, authorize view
      - Return resourceResponse with ReviewDTO
    - Implement update(UpdateReviewRequest $request, int $id): JsonResponse
      - Authorize update
      - Create UpdateReviewDTO from request
      - Call service updateReview
      - Return updatedResponse with ReviewDTO
    - Implement destroy(int $id): JsonResponse
      - Authorize delete
      - Call service deleteReview
      - Return deletedResponse
    - Implement myReviews(Request $request): JsonResponse
      - Get authenticated user's reviews
      - Return collectionResponse with pagination
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.6, 5.7, 8.1, 8.2, 8.3, 8.4, 8.6_

  - [ ] 8.2 Create ConsultantReviewsController
    - Inject ReviewService
    - Use ExceptionHandler and SuccessResponse traits
    - Implement index(Request $request, int $consultantId): JsonResponse
      - Get consultant reviews with pagination
      - Return collectionResponse
    - _Requirements: 5.5, 8.5_

- [ ] 9. Configure API Routes
  - [ ] 9.1 Add review routes to api.php
    - POST /api/reviews -> ReviewController@store (auth:sanctum)
    - GET /api/reviews/{id} -> ReviewController@show (auth:sanctum)
    - PUT /api/reviews/{id} -> ReviewController@update (auth:sanctum)
    - DELETE /api/reviews/{id} -> ReviewController@destroy (auth:sanctum)
    - GET /api/my/reviews -> ReviewController@myReviews (auth:sanctum)
    - GET /api/consultants/{id}/reviews -> ConsultantReviewsController@index (optional auth)
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_

- [ ] 10. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Write integration tests
  - [ ] 11.1 Write integration test for create-show-update-delete flow
    - Test complete CRUD lifecycle
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ] 11.2 Write integration test for consultant reviews endpoint
    - Test pagination and filtering
    - _Requirements: 5.5, 8.5_

  - [ ] 11.3 Write integration test for my reviews endpoint
    - Test pagination for authenticated client
    - _Requirements: 5.6, 8.6_

## Notes

- All tasks are required for comprehensive testing
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- All error messages should be in Arabic following existing project conventions
- The migration already exists at `database/migrations/2026_01_17_054850_create_reviews_table.php`
