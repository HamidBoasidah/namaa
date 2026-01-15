# Requirements Document

## Introduction

This document specifies the requirements for a robust Bookings backend system for a consultation platform. The system enables clients to book consultants either directly (hourly) or through specific consultant services (fixed duration). The platform operates in a single timezone (KSA) and handles availability checking, conflict prevention with race condition protection, pending booking holds, and cancellation tracking with polymorphic canceller support.

## Glossary

- **Booking**: A scheduled appointment between a Client and a Consultant for a specific time slot
- **Client**: A User who books consultations (the customer)
- **Consultant**: A service provider who offers consultations
- **ConsultantService**: A predefined service offered by a Consultant with fixed duration and price
- **Bookable**: Polymorphic reference to either Consultant (hourly booking) or ConsultantService (fixed duration booking)
- **Pending_Booking**: A booking in pending status that holds a slot for 15 minutes awaiting confirmation
- **Blocking_Booking**: A booking that blocks a time slot (confirmed OR pending with expires_at > now)
- **Buffer_After_Minutes**: Time buffer after a session that must be included in conflict checks
- **Occupied_Window**: The full time range blocked by a booking: [start_at, end_at + buffer_after_minutes]
- **Working_Hours**: Time slots when a Consultant is available for bookings on specific weekdays
- **Holiday**: A date when a Consultant is not available for bookings
- **Time_Granularity**: The minimum time unit (5 minutes) for start times and durations
- **Canceller**: The entity (Client, Consultant, or Admin) who cancelled a booking

## Requirements

### Requirement 1: Booking Data Model

**User Story:** As a system architect, I want a comprehensive booking data model, so that all booking information is properly stored and relationships are maintained.

#### Acceptance Criteria

1. THE Booking_Model SHALL store client_id as a foreign key to users table
2. THE Booking_Model SHALL store consultant_id as a foreign key to consultants table
3. THE Booking_Model SHALL support polymorphic bookable (bookable_type, bookable_id) for ConsultantService or Consultant
4. THE Booking_Model SHALL store start_at and end_at as datetime fields
5. THE Booking_Model SHALL store duration_minutes as unsigned small integer (multiples of 5)
6. THE Booking_Model SHALL store buffer_after_minutes as unsigned small integer snapshot (multiples of 5)
7. THE Booking_Model SHALL store status as enum: pending, confirmed, cancelled, completed, expired
8. THE Booking_Model SHALL store expires_at as nullable datetime for pending bookings
9. THE Booking_Model SHALL support polymorphic canceller (cancelled_by_type, cancelled_by_id)
10. THE Booking_Model SHALL store cancelled_at as nullable datetime
11. THE Booking_Model SHALL store cancel_reason as nullable string
12. THE Booking_Model SHALL store notes as nullable text
13. THE Booking_Model SHALL use soft deletes
14. THE Booking_Model SHALL have proper indexes for availability queries on consultant_id, start_at, end_at, status, and expires_at

### Requirement 2: Time Granularity Rules

**User Story:** As a platform operator, I want all booking times to follow 5-minute granularity, so that scheduling is consistent and predictable.

#### Acceptance Criteria

1. WHEN a booking start_at is provided, THE System SHALL validate it is a multiple of 5 minutes
2. WHEN a booking duration_minutes is provided, THE System SHALL validate it is a multiple of 5 minutes
3. WHEN a booking buffer_after_minutes is provided, THE System SHALL validate it is a multiple of 5 minutes
4. IF start_at is not a multiple of 5 minutes, THEN THE System SHALL reject the booking with a validation error
5. IF duration_minutes is not a multiple of 5 minutes, THEN THE System SHALL reject the booking with a validation error

### Requirement 3: Booking Types Support

**User Story:** As a client, I want to book either a consultant directly (hourly) or a specific consultant service, so that I can choose the booking type that suits my needs.

#### Acceptance Criteria

1. WHEN booking a Consultant directly, THE System SHALL accept user-provided duration_minutes (following platform rules)
2. WHEN booking a ConsultantService, THE System SHALL use the fixed duration_minutes stored on the service
3. WHEN booking a ConsultantService, THE System SHALL use the buffer from the service if available, otherwise from consultant
4. WHEN booking a Consultant directly, THE System SHALL use the buffer from the consultant
5. THE System SHALL store the buffer_after_minutes as a snapshot at booking creation time

### Requirement 4: Availability Validation - Working Hours

**User Story:** As a client, I want bookings to only be allowed during consultant working hours, so that I can only book when the consultant is available.

#### Acceptance Criteria

1. WHEN creating a booking, THE System SHALL verify the booking falls within consultant's active working hours for that weekday
2. IF the booking start_at is before the consultant's working hours start_time, THEN THE System SHALL reject the booking
3. IF the booking end_at (including buffer) exceeds the consultant's working hours end_time, THEN THE System SHALL reject the booking
4. IF the consultant has no active working hours for the booking weekday, THEN THE System SHALL reject the booking
5. WHEN a consultant has multiple working hour slots on the same day, THE System SHALL allow booking within any of those slots

### Requirement 5: Availability Validation - Holidays

**User Story:** As a client, I want bookings to be blocked on consultant holidays, so that I don't book when the consultant is unavailable.

#### Acceptance Criteria

1. WHEN creating a booking, THE System SHALL check if the booking date falls on a consultant holiday
2. IF the booking date matches a consultant holiday, THEN THE System SHALL reject the booking with appropriate message
3. THE System SHALL check holidays by date only (not time)

### Requirement 6: Conflict Prevention - Overlapping Bookings

**User Story:** As a platform operator, I want to prevent double-booking of consultants, so that scheduling conflicts are avoided.

#### Acceptance Criteria

1. WHEN creating a booking, THE System SHALL check for overlapping blocking bookings for the same consultant
2. THE System SHALL consider a booking as blocking IF status is 'confirmed' OR (status is 'pending' AND expires_at > now)
3. THE System SHALL calculate occupied_window as [start_at, end_at + buffer_after_minutes] for overlap checks
4. IF the new booking's occupied_window overlaps with any existing blocking booking's occupied_window, THEN THE System SHALL reject the booking
5. THE System SHALL NOT consider cancelled, expired, or completed bookings as blocking

### Requirement 7: Race Condition Prevention

**User Story:** As a platform operator, I want to prevent race conditions when creating bookings, so that two clients cannot book the same slot simultaneously.

#### Acceptance Criteria

1. WHEN creating a pending booking, THE System SHALL use database transaction with consultant-level locking
2. WHEN confirming a booking, THE System SHALL use database transaction with consultant-level locking
3. WHILE holding the lock, THE System SHALL re-check all availability and conflict conditions before writing
4. IF conflicts are detected during re-check, THEN THE System SHALL rollback and return appropriate error
5. THE System SHALL use pessimistic locking (SELECT FOR UPDATE) on the consultant record

### Requirement 8: Pending Booking Hold

**User Story:** As a client, I want my booking to be held for 15 minutes while I complete the process, so that the slot is reserved for me temporarily.

#### Acceptance Criteria

1. WHEN a booking is created, THE System SHALL set status to 'pending' and expires_at to now + 15 minutes
2. WHILE a booking is pending with expires_at > now, THE System SHALL treat it as blocking for conflict checks
3. WHEN expires_at <= now for a pending booking, THE System SHALL NOT treat it as blocking
4. THE System SHALL provide a scheduled job to mark expired pending bookings as 'expired'

### Requirement 9: Booking Confirmation

**User Story:** As a client, I want to confirm my pending booking, so that the slot is permanently reserved for me.

#### Acceptance Criteria

1. WHEN confirming a booking, THE System SHALL verify the booking status is 'pending'
2. WHEN confirming a booking, THE System SHALL verify expires_at > now (not expired)
3. WHEN confirming a booking, THE System SHALL re-check availability and conflicts under lock
4. IF confirmation is successful, THE System SHALL set status to 'confirmed' and clear expires_at
5. IF the booking has expired, THEN THE System SHALL reject confirmation with appropriate error
6. IF conflicts are detected during confirmation, THEN THE System SHALL reject with appropriate error

### Requirement 10: Booking Cancellation

**User Story:** As a user, I want to cancel bookings and track who cancelled them, so that cancellation history is maintained.

#### Acceptance Criteria

1. WHEN cancelling a booking, THE System SHALL set status to 'cancelled'
2. WHEN cancelling a booking, THE System SHALL set cancelled_at to current timestamp
3. WHEN cancelling a booking, THE System SHALL store the canceller using polymorphic fields (cancelled_by_type, cancelled_by_id)
4. WHEN cancelling a booking, THE System SHALL optionally store cancel_reason
5. THE System SHALL support cancellation by Client (User), Consultant (User), or Admin
6. WHEN a booking is cancelled, THE System SHALL NOT treat it as blocking for future bookings

### Requirement 11: Pending Booking Expiration Job

**User Story:** As a platform operator, I want expired pending bookings to be automatically marked as expired, so that held slots are released.

#### Acceptance Criteria

1. THE System SHALL provide a scheduled command/job to expire old pending bookings
2. THE Job SHALL find all bookings WHERE status = 'pending' AND expires_at <= now
3. THE Job SHALL update matching bookings to status = 'expired'
4. THE Job SHALL be schedulable to run every minute or every 5 minutes
5. THE Job SHALL handle batch processing efficiently

### Requirement 12: Available Slots Query

**User Story:** As a client, I want to see available time slots for a consultant on a specific date, so that I can choose when to book.

#### Acceptance Criteria

1. WHEN querying available slots, THE System SHALL accept consultant_id, date, and optionally bookable_type/bookable_id
2. THE System SHALL return available time slots based on consultant's working hours for that weekday
3. THE System SHALL exclude slots that overlap with blocking bookings (including buffer)
4. THE System SHALL exclude the entire day if it falls on a consultant holiday
5. THE System SHALL return slots in 5-minute granularity
6. THE System SHALL consider the duration and buffer of the bookable when calculating available slots

### Requirement 13: Authorization and Access Control

**User Story:** As a platform operator, I want proper access control on bookings, so that users can only access their own data.

#### Acceptance Criteria

1. THE System SHALL allow clients to view only their own bookings
2. THE System SHALL allow consultants to view only bookings for their consultations
3. THE System SHALL allow clients to cancel only their own bookings
4. THE System SHALL allow consultants to cancel only their own consultation bookings
5. THE System SHALL allow admins to view and cancel any booking
6. WHEN creating a booking, THE System SHALL verify the authenticated user is the client

### Requirement 14: API Endpoints

**User Story:** As a developer, I want well-defined API endpoints for booking operations, so that the frontend can integrate properly.

#### Acceptance Criteria

1. THE System SHALL provide POST /api/bookings/pending endpoint to create pending booking
2. THE System SHALL provide POST /api/bookings/{id}/confirm endpoint to confirm booking
3. THE System SHALL provide GET /api/consultants/{id}/available-slots endpoint with date and bookable parameters
4. THE System SHALL provide POST /api/bookings/{id}/cancel endpoint to cancel booking
5. THE System SHALL provide GET /api/bookings endpoint to list user's bookings
6. THE System SHALL provide GET /api/bookings/{id} endpoint to view booking details
7. ALL endpoints SHALL follow existing project response format conventions

### Requirement 15: Data Transfer Objects

**User Story:** As a developer, I want proper DTOs for booking operations, so that data is properly structured and validated.

#### Acceptance Criteria

1. THE System SHALL provide CreatePendingBookingDTO for pending booking creation
2. THE System SHALL provide ConfirmBookingDTO for booking confirmation
3. THE System SHALL provide GetAvailableSlotsDTO for availability queries
4. THE System SHALL provide BookingDTO for booking responses
5. ALL DTOs SHALL follow existing project DTO patterns (extending BaseDTO)
