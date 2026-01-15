# Implementation Plan: Bookings Backend System

## Overview

This implementation plan breaks down the Bookings backend system into discrete, incremental tasks. Each task builds on previous work and includes specific file paths and requirements references. The implementation follows the existing project's DTO + Repository + Service architecture.

## Tasks

- [x] 1. Update Booking Migration and Model
  - [x] 1.1 Update the bookings migration file with complete schema
    - Update `database/migrations/2026_01_13_164343_create_bookings_table.php`
    - Remove payment-related fields (price, commission, payment_status, etc.)
    - Ensure all required fields: client_id, consultant_id, bookable morphs, start_at, end_at, duration_minutes, buffer_after_minutes, status enum, expires_at, cancelled_at, cancel_reason, cancelled_by morphs, notes
    - Add proper indexes for availability queries
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11, 1.12, 1.13, 1.14_

  - [x] 1.2 Implement Booking Model with relationships and scopes
    - Update `app/Models/Booking.php`
    - Add status constants (PENDING, CONFIRMED, CANCELLED, COMPLETED, EXPIRED)
    - Add fillable fields and casts
    - Implement relationships: client(), consultant(), bookable(), cancelledBy()
    - Implement scopes: scopeBlocking(), scopeForConsultant(), scopeOverlapping()
    - _Requirements: 1.1, 1.2, 1.3, 1.9, 6.2_

  - [x] 1.3 Write property test for Booking Model polymorphic relationships
    - **Property 1: Time Granularity Validation** (partial - model level)
    - **Validates: Requirements 1.3, 1.7, 1.9**

- [x] 2. Implement DTOs
  - [x] 2.1 Create CreatePendingBookingDTO
    - Create `app/DTOs/CreatePendingBookingDTO.php`
    - Include: client_id, consultant_id, bookable_type, bookable_id, start_at, duration_minutes, notes
    - Implement fromRequest() static method
    - _Requirements: 15.1_

  - [x] 2.2 Create ConfirmBookingDTO
    - Create `app/DTOs/ConfirmBookingDTO.php`
    - Include: booking_id, client_id
    - Implement fromRequest() static method
    - _Requirements: 15.2_

  - [x] 2.3 Create GetAvailableSlotsDTO
    - Create `app/DTOs/GetAvailableSlotsDTO.php`
    - Include: consultant_id, date, bookable_type, bookable_id
    - Implement fromRequest() static method
    - _Requirements: 15.3_

  - [x] 2.4 Create BookingDTO for responses
    - Create `app/DTOs/BookingDTO.php`
    - Include all booking fields for API responses
    - Implement fromModel() and toArray() methods
    - Follow existing DTO patterns (ConsultantDTO)
    - _Requirements: 15.4, 15.5_

- [x] 3. Implement BookingRepository
  - [x] 3.1 Create BookingRepository with base methods
    - Create `app/Repositories/BookingRepository.php`
    - Extend BaseRepository
    - Implement createPending(), findById(), forConsultant()
    - _Requirements: 1.1, 1.2, 1.3_

  - [x] 3.2 Implement findBlockingOverlaps method
    - Add method to find blocking bookings that overlap with time range
    - Blocking = confirmed OR (pending AND expires_at > now)
    - Include buffer_after_minutes in overlap calculation
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 3.3 Write property test for overlap detection
    - **Property 6: Overlap Detection with Blocking Status**
    - **Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

  - [x] 3.4 Implement confirm, cancel, and expirePending methods
    - confirm(): set status to confirmed, clear expires_at
    - cancel(): set status, cancelled_at, cancelled_by morphs, cancel_reason
    - expirePending(): bulk update expired pending bookings
    - _Requirements: 9.4, 10.1, 10.2, 10.3, 11.2, 11.3_

  - [x] 3.5 Register BookingRepository in RepositoryServiceProvider
    - Update `app/Providers/RepositoryServiceProvider.php`

- [x] 4. Checkpoint - Ensure model and repository tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implement AvailabilityService
  - [x] 5.1 Create AvailabilityService with holiday and working hours checks
    - Create `app/Services/AvailabilityService.php`
    - Inject ConsultantHolidayRepository, ConsultantWorkingHourRepository, BookingRepository
    - Implement isHoliday() method
    - Implement getWorkingHoursForDay() method
    - Implement fitsInWorkingHours() method
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3_

  - [x] 5.2 Write property test for working hours validation
    - **Property 4: Working Hours Validation**
    - **Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.5**

  - [x] 5.3 Write property test for holiday validation
    - **Property 5: Holiday Validation**
    - **Validates: Requirements 5.1, 5.2, 5.3**

  - [x] 5.4 Implement validateSlot method
    - Check holiday, working hours, and overlapping bookings
    - Return ['valid' => bool, 'reason' => string|null]
    - _Requirements: 4.1, 5.1, 6.1_

  - [x] 5.5 Implement getAvailableSlots method
    - Accept consultant_id, date, bookable_type, bookable_id, granularity
    - Return array of available slot start times
    - Consider duration and buffer when calculating slots
    - Return empty array if holiday
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_

  - [x] 5.6 Write property test for available slots calculation
    - **Property 11: Available Slots Calculation**
    - **Validates: Requirements 12.2, 12.3, 12.4, 12.5, 12.6**

- [x] 6. Implement BookingService
  - [x] 6.1 Create BookingService with dependency injection
    - Create `app/Services/BookingService.php`
    - Inject BookingRepository, AvailabilityService, ConsultantRepository, ConsultantServiceRepository
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [x] 6.2 Implement resolveDurationAndBuffer helper method
    - For consultant_service: use service duration, service buffer (or consultant buffer)
    - For consultant: use user-provided duration, consultant buffer
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [x] 6.3 Write property test for duration and buffer resolution
    - **Property 2: Booking Type Duration Resolution**
    - **Property 3: Buffer Resolution and Snapshot**
    - **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**

  - [x] 6.4 Implement createPending method with transaction and locking
    - Use DB::transaction with pessimistic locking on consultant
    - Validate time granularity (5-minute rule)
    - Resolve duration and buffer
    - Validate slot availability (working hours, holidays, conflicts)
    - Create booking with status=pending, expires_at=now+15min
    - _Requirements: 2.1, 2.2, 7.1, 7.3, 7.4, 7.5, 8.1_

  - [x] 6.5 Write property test for time granularity validation
    - **Property 1: Time Granularity Validation**
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**

  - [x] 6.6 Write property test for pending booking lifecycle
    - **Property 7: Pending Booking Lifecycle**
    - **Validates: Requirements 8.1, 8.2, 8.3**

  - [x] 6.7 Implement confirm method with transaction and locking
    - Verify booking is pending and not expired
    - Re-check availability under lock
    - Set status=confirmed, clear expires_at
    - _Requirements: 7.2, 7.3, 7.4, 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

  - [x] 6.8 Write property test for confirmation state transitions
    - **Property 9: Confirmation State Transitions**
    - **Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5, 9.6**

  - [x] 6.9 Implement cancel method with polymorphic canceller
    - Set status=cancelled, cancelled_at, cancelled_by morphs, cancel_reason
    - Support User (client/consultant) and Admin as cancellers
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

  - [x] 6.10 Write property test for cancellation
    - **Property 10: Cancellation with Polymorphic Canceller**
    - **Validates: Requirements 10.1, 10.2, 10.3, 10.5, 10.6**

  - [x] 6.11 Implement expireOldPending method
    - Find all pending bookings where expires_at <= now
    - Update status to expired
    - Return count of expired bookings
    - _Requirements: 11.1, 11.2, 11.3, 11.5_

  - [x] 6.12 Write property test for expiration job
    - **Property 8: Expiration Job Correctness**
    - **Validates: Requirements 11.2, 11.3**

- [x] 7. Checkpoint - Ensure service layer tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implement Form Requests
  - [x] 8.1 Create StorePendingBookingRequest with 5-minute validation
    - Create `app/Http/Requests/StorePendingBookingRequest.php`
    - Validate consultant_id, bookable_type, bookable_id, start_at, duration_minutes, notes
    - Add custom rule for 5-minute granularity on start_at
    - Add custom rule for 5-minute granularity on duration_minutes
    - Add validation that bookable exists and belongs to consultant
    - _Requirements: 2.1, 2.2, 2.4, 2.5, 3.1_

  - [x] 8.2 Create ConfirmBookingRequest
    - Create `app/Http/Requests/ConfirmBookingRequest.php`
    - Minimal validation - booking existence checked in controller
    - _Requirements: 9.1_

  - [x] 8.3 Create CancelBookingRequest
    - Create `app/Http/Requests/CancelBookingRequest.php`
    - Validate optional reason field
    - _Requirements: 10.4_

  - [x] 8.4 Create GetAvailableSlotsRequest
    - Create `app/Http/Requests/GetAvailableSlotsRequest.php`
    - Validate date (Y-m-d, after_or_equal:today)
    - Validate optional bookable_type and bookable_id
    - _Requirements: 12.1_

- [x] 9. Implement BookingPolicy
  - [x] 9.1 Create BookingPolicy with authorization rules
    - Create `app/Policies/BookingPolicy.php`
    - Implement view(): client owns booking OR consultant owns consultation OR admin
    - Implement create(): authenticated user
    - Implement cancel(): client owns booking OR consultant owns consultation OR admin
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_

  - [x] 9.2 Write property test for authorization rules
    - **Property 12: Authorization Invariants**
    - **Validates: Requirements 13.1, 13.2, 13.3, 13.4, 13.5, 13.6**

  - [x] 9.3 Register BookingPolicy in AuthServiceProvider
    - Update `app/Providers/AuthServiceProvider.php` or use auto-discovery

- [x] 10. Implement Controllers
  - [x] 10.1 Create BookingController with CRUD operations
    - Create `app/Http/Controllers/Api/BookingController.php`
    - Use ExceptionHandler and SuccessResponse traits
    - Implement storePending() - POST /api/bookings/pending
    - Implement confirm() - POST /api/bookings/{id}/confirm
    - Implement cancel() - POST /api/bookings/{id}/cancel
    - Implement index() - GET /api/bookings
    - Implement show() - GET /api/bookings/{id}
    - _Requirements: 14.1, 14.2, 14.4, 14.5, 14.6, 14.7_

  - [x] 10.2 Create ConsultantAvailabilityController
    - Create `app/Http/Controllers/Api/ConsultantAvailabilityController.php`
    - Implement availableSlots() - GET /api/consultants/{id}/available-slots
    - _Requirements: 14.3_

- [x] 11. Configure API Routes
  - [x] 11.1 Add booking routes to api.php
    - Update `routes/api.php`
    - POST /api/bookings/pending
    - POST /api/bookings/{id}/confirm
    - POST /api/bookings/{id}/cancel
    - GET /api/bookings
    - GET /api/bookings/{id}
    - GET /api/consultants/{id}/available-slots
    - Apply auth:sanctum middleware
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6_

- [x] 12. Implement Scheduled Job for Expiring Pending Bookings
  - [x] 12.1 Create ExpirePendingBookingsCommand
    - Create `app/Console/Commands/ExpirePendingBookingsCommand.php`
    - Signature: bookings:expire-pending
    - Call BookingService::expireOldPending()
    - Log count of expired bookings
    - _Requirements: 11.1, 11.4_

  - [x] 12.2 Register command in scheduler
    - Update `routes/console.php` or `app/Console/Kernel.php`
    - Schedule to run every minute: $schedule->command('bookings:expire-pending')->everyMinute()
    - _Requirements: 11.4_

- [x] 13. Final Checkpoint - Run all tests and verify API endpoints
  - ✅ All 57 tests pass (124 assertions)
  - ✅ All API routes verified and working
  - ✅ Scheduled command registered (runs every minute)

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- The implementation follows existing project patterns (DTO + Repository + Service)
- All API responses follow existing SuccessResponse trait format
- Arabic error messages are used for consistency with existing codebase
