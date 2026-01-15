# Design Document: Booking Race Condition Fix

## Overview

هذا المستند يصف التصميم التقني لإصلاح ثغرة Race Condition في نظام الحجوزات. المشكلة الحالية تسمح بإنشاء حجوزات متعددة لنفس المستشار في نفس الوقت بسبب عدم كفاية آلية القفل الحالية.

### تحليل المشكلة

الكود الحالي يستخدم `lockForUpdate()` على سجل المستشار، لكن هذا غير كافٍ لأن:
1. القفل على سجل المستشار لا يمنع قراءة الحجوزات الموجودة بشكل متزامن
2. التحقق من التعارض يحدث بعد القفل لكن قبل الإدراج، مما يسمح بـ race condition
3. لا يوجد قيد على مستوى قاعدة البيانات يمنع التعارضات

### الحل المقترح

1. **تحسين آلية القفل**: استخدام `LOCK IN SHARE MODE` أو `FOR UPDATE` على الحجوزات المتعارضة المحتملة
2. **إضافة فحص إضافي**: التحقق من التعارض مرة أخرى بعد القفل مباشرة
3. **استخدام Unique Constraint**: إضافة قيد فريد على مستوى قاعدة البيانات (اختياري)

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      BookingService                              │
├─────────────────────────────────────────────────────────────────┤
│  createPending()                                                 │
│  ├── DB::transaction()                                          │
│  │   ├── Lock consultant row (FOR UPDATE)                       │
│  │   ├── Lock potential conflicting bookings (FOR UPDATE)       │
│  │   ├── Validate slot availability                             │
│  │   └── Create booking                                         │
│  └── Return booking or throw ValidationException                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    BookingRepository                             │
├─────────────────────────────────────────────────────────────────┤
│  findBlockingOverlapsWithLock()                                 │
│  ├── Query blocking bookings in time range                      │
│  ├── Apply FOR UPDATE lock                                      │
│  └── Return locked collection                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Database Layer                              │
├─────────────────────────────────────────────────────────────────┤
│  bookings table                                                  │
│  ├── Row-level locks (InnoDB)                                   │
│  └── Indexes for efficient conflict queries                     │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### BookingRepository - New Method

```php
/**
 * Find blocking bookings with pessimistic lock
 * This prevents race conditions by locking potential conflicts
 * 
 * @param int $consultantId
 * @param Carbon $occupiedStart
 * @param Carbon $occupiedEnd
 * @param int|null $excludeBookingId
 * @return Collection Locked collection of conflicting bookings
 */
public function findBlockingOverlapsWithLock(
    int $consultantId,
    Carbon $occupiedStart,
    Carbon $occupiedEnd,
    ?int $excludeBookingId = null
): Collection;
```

### BookingService - Updated createPending()

```php
public function createPending(CreatePendingBookingDTO $dto): Booking
{
    return DB::transaction(function () use ($dto) {
        // 1. Lock consultant record
        $consultant = Consultant::lockForUpdate()->findOrFail($dto->consultant_id);
        
        // 2. Lock potential conflicting bookings (NEW)
        $conflicts = $this->bookings->findBlockingOverlapsWithLock(
            $consultant->id,
            $startAt,
            $occupiedEnd
        );
        
        // 3. If conflicts exist, reject
        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'start_at' => ['الموعد المحدد غير متاح'],
            ]);
        }
        
        // 4. Create booking
        return $this->bookings->createPending($data);
    });
}
```

## Data Models

### Booking Model - No Changes Required

الـ model الحالي يحتوي على جميع الحقول المطلوبة:
- `consultant_id`: معرف المستشار
- `start_at`: وقت البداية
- `end_at`: وقت النهاية
- `buffer_after_minutes`: وقت الراحة
- `status`: حالة الحجز
- `expires_at`: وقت انتهاء صلاحية الحجز المعلق

### Database Indexes - Existing

الفهارس الموجودة كافية:
- `consultant_id, start_at`
- `consultant_id, end_at`
- `consultant_id, status`

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: No Overlapping Blocking Bookings

*For any* consultant and *for any* two blocking bookings B1 and B2 for that consultant, the occupied windows of B1 and B2 must not overlap.

**Validates: Requirements 1.1, 1.2, 2.2, 4.3**

### Property 2: Concurrent Booking Requests - Single Acceptance

*For any* set of N concurrent booking requests for the same consultant and overlapping time slots, exactly one request should succeed and N-1 requests should fail with a conflict error.

**Validates: Requirements 1.1, 3.1, 3.2**

### Property 3: Buffer Inclusion in Conflict Detection

*For any* booking with buffer_after_minutes > 0, the conflict detection must consider the occupied window (start_at to end_at + buffer_after_minutes) when checking for overlaps.

**Validates: Requirements 2.1, 2.2**

### Property 4: Transaction Atomicity

*For any* failed booking creation attempt, no partial data should be persisted to the database.

**Validates: Requirements 4.1, 4.2**

### Property 5: Blocking Status Correctness

*For any* booking, it is considered blocking if and only if:
- status = 'confirmed', OR
- status = 'pending' AND expires_at > now()

**Validates: Requirements 2.3**

## Error Handling

### Conflict Detection Errors

```php
// When a time slot conflict is detected
throw ValidationException::withMessages([
    'start_at' => ['الموعد المحدد غير متاح - يوجد حجز آخر في هذا الوقت'],
]);
```

### Database Lock Timeout

```php
// If lock acquisition times out (rare)
try {
    // ... locking code
} catch (QueryException $e) {
    if ($e->getCode() === '40001') { // Deadlock
        throw ValidationException::withMessages([
            'booking' => ['حدث خطأ أثناء معالجة الحجز، يرجى المحاولة مرة أخرى'],
        ]);
    }
    throw $e;
}
```

## Testing Strategy

### Unit Tests

1. **Conflict Detection Tests**
   - Test that overlapping bookings are detected
   - Test that buffer is included in overlap calculation
   - Test that non-overlapping bookings are allowed

2. **Status Tests**
   - Test that only blocking statuses are considered
   - Test that expired pending bookings are not blocking

### Property-Based Tests

سنستخدم مكتبة **PHPUnit** مع **Faker** لإنشاء بيانات عشوائية للاختبارات.

1. **Property 1 Test**: No Overlapping Blocking Bookings
   - Generate random bookings for a consultant
   - Verify no two blocking bookings have overlapping occupied windows

2. **Property 2 Test**: Concurrent Booking Requests
   - Simulate concurrent requests using database transactions
   - Verify only one succeeds

3. **Property 3 Test**: Buffer Inclusion
   - Generate bookings with various buffer values
   - Verify buffer is always included in conflict detection

### Integration Tests

1. **Race Condition Test**
   - Use multiple database connections/processes
   - Attempt to create conflicting bookings simultaneously
   - Verify only one succeeds

### Test Configuration

- Minimum 100 iterations per property test
- Each test tagged with: **Feature: booking-race-condition-fix, Property N: [property_text]**
