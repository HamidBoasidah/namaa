# Implementation Plan: Booking Race Condition Fix

## Overview

هذه الخطة تصف خطوات تنفيذ إصلاح ثغرة Race Condition في نظام الحجوزات. سنقوم بتحسين آلية القفل وإضافة اختبارات للتحقق من سلامة النظام.

## Tasks

- [x] 1. إضافة method جديدة للقفل في BookingRepository
  - [x] 1.1 إضافة `findBlockingOverlapsWithLock()` method
    - إضافة method تستخدم `lockForUpdate()` على الحجوزات المتعارضة
    - يجب أن تدعم SQLite و MySQL
    - _Requirements: 1.4, 4.1_

  - [ ]* 1.2 كتابة property test للتحقق من عدم وجود حجوزات متداخلة
    - **Property 1: No Overlapping Blocking Bookings**
    - **Validates: Requirements 1.2, 2.2, 4.3**

- [x] 2. تحديث BookingService لاستخدام القفل الجديد
  - [x] 2.1 تحديث `createPending()` method
    - استخدام `findBlockingOverlapsWithLock()` بدلاً من `validateSlot()`
    - التأكد من القفل قبل التحقق من التعارض
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 2.2 تحديث `confirm()` method
    - استخدام القفل الجديد عند إعادة التحقق من التعارض
    - _Requirements: 1.2, 2.2_

  - [x] 2.3 تحديث `create()` method (للأدمن)
    - استخدام القفل الجديد للتحقق من التعارض
    - _Requirements: 1.2, 2.2_

- [x] 3. Checkpoint - التأكد من عمل الكود
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. كتابة اختبارات Race Condition
  - [x] 4.1 كتابة اختبار concurrent booking requests
    - محاكاة طلبات متزامنة باستخدام database transactions
    - التحقق من قبول حجز واحد فقط
    - _Requirements: 1.1, 3.1, 3.2_

  - [ ]* 4.2 كتابة property test للـ concurrent requests
    - **Property 2: Concurrent Booking Single Acceptance**
    - **Validates: Requirements 1.1, 3.1, 3.2**

- [ ] 5. كتابة اختبارات Buffer Inclusion
  - [ ]* 5.1 كتابة property test للتحقق من احتساب Buffer
    - **Property 3: Buffer Inclusion in Conflict Detection**
    - **Validates: Requirements 2.1**

- [ ] 6. كتابة اختبارات Transaction Atomicity
  - [ ]* 6.1 كتابة property test للتحقق من atomicity
    - **Property 4: Transaction Atomicity**
    - **Validates: Requirements 4.1, 4.2**

- [ ] 7. كتابة اختبارات Blocking Status
  - [ ]* 7.1 كتابة property test للتحقق من Blocking Status
    - **Property 5: Blocking Status Correctness**
    - **Validates: Requirements 2.3**

- [x] 8. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
