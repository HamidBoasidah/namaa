# Requirements Document: Reviews & Ratings Backend System

## Introduction

This document specifies the requirements for a robust Reviews backend system for a consultation platform. The system allows Clients to leave a single review per Booking after the booking is completed. The platform operates in a single timezone (KSA). This scope is BACKEND ONLY (no frontend). The implementation MUST follow the existing project architecture and conventions (DTO + Repository + Service), and integrate with the existing Bookings, Consultants, and Users models.

Payments are OUT OF SCOPE and must not be referenced.

## Glossary

- **Review**: A rating and optional comment written by a Client about a completed Booking
- **Rating**: A numeric score (1..5)
- **Client**: A User who booked the consultation
- **Consultant**: The service provider being reviewed
- **Booking**: The appointment that the Review is linked to (bookings table with start_at/end_at/status)
- **One_Review_Per_Booking**: A constraint ensuring only one review exists for each booking
- **Soft_Delete**: Logical deletion using deleted_at timestamp
- **Ownership**: Only the booking's client can create/update/delete their review

---

## Requirements

### Requirement 1: Review Data Model

**User Story:** As a system architect, I want a comprehensive review data model so that reviews are stored consistently and can be queried efficiently for consultant profiles.

#### Acceptance Criteria

1. THE Review model SHALL store booking_id as a foreign key to bookings table
2. THE Review model SHALL store consultant_id as a foreign key to consultants table
3. THE Review model SHALL store client_id as a foreign key to users table
4. THE Review model SHALL store rating as unsigned tiny integer (1..5) and MUST NOT be null
5. THE Review model SHALL store comment as nullable text
6. THE Review model SHALL use SoftDeletes trait
7. THE reviews table SHALL enforce One_Review_Per_Booking using a unique index on booking_id
8. THE reviews table SHALL have indexes for fast lookups by consultant_id and client_id
9. THE Review model SHALL NOT include reply_from_consultant or any consultant reply fields

---

### Requirement 2: Data Consistency with Booking

**User Story:** As a platform operator, I want consultant_id and client_id stored in reviews for fast retrieval, but always consistent with the related booking.

#### Acceptance Criteria

1. WHEN creating a review, THE System SHALL derive consultant_id and client_id from the referenced booking
2. THE System SHALL NOT accept consultant_id or client_id from client input
3. THE System SHALL validate that the booking belongs to the authenticated client before creating the review
4. THE System SHALL ensure reviews.consultant_id equals bookings.consultant_id and reviews.client_id equals bookings.client_id
5. IF booking_id is invalid or booking not accessible, THEN THE System SHALL return a validation error

---

### Requirement 3: Review Eligibility Rules

**User Story:** As a client, I want to review only completed bookings, so that reviews reflect actual completed consultations.

#### Acceptance Criteria

1. THE System SHALL allow review creation only if booking.status equals 'completed'
2. IF booking.status is not 'completed', THEN THE System SHALL reject review creation with an appropriate error message
3. THE System SHALL allow only one review per booking enforced by unique constraint
4. IF a review already exists for the booking, THEN THE System SHALL reject creating another review with an appropriate error message

---

### Requirement 4: Rating Validation Rules

**User Story:** As a platform operator, I want rating values to be valid and consistent.

#### Acceptance Criteria

1. THE System SHALL validate rating is an integer between 1 and 5 inclusive
2. IF rating is outside 1..5 range, THEN THE System SHALL reject with validation error
3. THE System SHALL validate comment is optional but if present MUST be within 2000 characters maximum

---

### Requirement 5: CRUD Operations

**User Story:** As a client, I want to create, view, update, and delete my review so that I can manage my feedback.

#### Acceptance Criteria

1. THE System SHALL provide an endpoint to create a review for a booking
2. THE System SHALL provide an endpoint to update an existing review (rating and comment)
3. THE System SHALL provide an endpoint to soft-delete a review
4. THE System SHALL provide an endpoint to view a single review
5. THE System SHALL provide an endpoint to list reviews for a consultant (public profile listing with pagination)
6. THE System SHALL provide an endpoint to list reviews for the authenticated client (my reviews with pagination)
7. THE System SHALL follow existing project response format conventions

---

### Requirement 6: Authorization and Access Control

**User Story:** As a platform operator, I want proper access control so users cannot manipulate other users' reviews.

#### Acceptance Criteria

1. WHEN a client attempts to create a review, THE System SHALL verify the booking belongs to that client
2. WHEN a client attempts to update a review, THE System SHALL verify the review belongs to that client
3. WHEN a client attempts to delete a review, THE System SHALL verify the review belongs to that client
4. THE System SHALL allow consultants to view reviews about themselves but SHALL NOT allow edit or delete
5. THE System SHALL allow admins to view and soft-delete any review
6. IF an unauthorized action is attempted, THEN THE System SHALL return 403 Forbidden response

---

### Requirement 7: Repository Service DTO Architecture

**User Story:** As a system owner, I want the implementation to follow the existing project patterns (DTO/Repository/Service).

#### Acceptance Criteria

1. THE System SHALL implement ReviewRepository following existing BaseRepository patterns
2. THE System SHALL implement ReviewService with createReview, updateReview, deleteReview, getConsultantReviews, and getMyReviews methods
3. THE System SHALL implement DTOs following existing BaseDTO patterns
4. THE System SHALL implement FormRequests for validation following existing project style

---

### Requirement 8: API Endpoints

**User Story:** As a developer, I want clean API endpoints for reviews.

#### Acceptance Criteria

1. THE System SHALL provide POST /api/reviews endpoint to create review with booking_id, rating, and optional comment
2. THE System SHALL provide PUT /api/reviews/{id} endpoint to update review rating and comment
3. THE System SHALL provide DELETE /api/reviews/{id} endpoint to soft delete review
4. THE System SHALL provide GET /api/reviews/{id} endpoint to show single review
5. THE System SHALL provide GET /api/consultants/{id}/reviews endpoint to list consultant reviews with pagination
6. THE System SHALL provide GET /api/my/reviews endpoint to list authenticated client reviews with pagination
7. THE System SHALL use existing auth middleware and response format

---

### Requirement 9: Model Relationships

**User Story:** As a developer, I want correct Eloquent relationships for easy querying.

#### Acceptance Criteria

1. THE Review model SHALL define belongsTo relationship with Booking
2. THE Review model SHALL define belongsTo relationship with Consultant
3. THE Review model SHALL define belongsTo relationship with User as client
4. THE Booking model SHALL define hasOne relationship with Review
5. THE Consultant model SHALL define hasMany relationship with Reviews
6. THE User model SHALL define hasMany relationship with Reviews as clientReviews

Input rules:
- Create Review accepts ONLY: booking_id, rating, comment.
- consultant_id and client_id MUST be derived from booking in the service layer and never accepted from request payload.


---

## Deliverables
The AI MUST generate ALL backend files with full code and correct paths, following the existing project conventions:
- Review migration (if not already created) + Review model
- ReviewRepository
- ReviewService
- DTOs: CreateReviewDTO, UpdateReviewDTO, DeleteReviewDTO, GetConsultantReviewsDTO, GetMyReviewsDTO, ReviewDTO
- FormRequests
- Controllers
- Routes (api.php)
- ReviewPolicy
- Pagination according to existing conventions
- (Optional) tests if the project already has a testing structure