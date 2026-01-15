# Requirements Document

## Introduction

هذا المستند يحدد متطلبات إصلاح ثغرة Race Condition في نظام الحجوزات. المشكلة الحالية تسمح بإنشاء حجوزات متعددة لنفس المستشار في نفس الوقت، مما يؤدي إلى تعارضات في الجدول الزمني.

## Glossary

- **Booking_System**: نظام إدارة الحجوزات المسؤول عن إنشاء وتأكيد وإلغاء الحجوزات
- **Consultant**: المستشار الذي يتم حجز مواعيد معه
- **Time_Slot**: فترة زمنية محددة ببداية ونهاية
- **Race_Condition**: حالة تحدث عند محاولة عمليات متعددة الوصول لنفس المورد في نفس الوقت
- **Blocking_Booking**: حجز يحجز فترة زمنية (حالة pending صالحة أو confirmed)
- **Pessimistic_Lock**: قفل على مستوى قاعدة البيانات يمنع التعديلات المتزامنة
- **Occupied_Window**: الفترة الزمنية الكاملة للحجز شاملة وقت الراحة (buffer)

## Requirements

### Requirement 1: منع الحجوزات المتعارضة

**User Story:** كمستخدم للنظام، أريد أن يمنع النظام إنشاء حجوزات متعارضة لنفس المستشار، حتى لا يحدث تضارب في المواعيد.

#### Acceptance Criteria

1. WHEN طلبات حجز متعددة تصل في نفس الوقت لنفس المستشار ونفس الفترة الزمنية THEN THE Booking_System SHALL يقبل حجز واحد فقط ويرفض الباقي
2. WHEN يتم إنشاء حجز جديد THEN THE Booking_System SHALL يتحقق من عدم وجود حجوزات blocking متعارضة
3. WHEN يتم اكتشاف تعارض في الوقت THEN THE Booking_System SHALL يرفض الحجز الجديد مع رسالة خطأ واضحة
4. THE Booking_System SHALL يستخدم pessimistic locking على مستوى الصف لمنع race conditions

### Requirement 2: التحقق من التعارض مع مراعاة Buffer

**User Story:** كمستشار، أريد أن يحترم النظام وقت الراحة بين الحجوزات، حتى أحصل على استراحة كافية.

#### Acceptance Criteria

1. WHEN يتم التحقق من تعارض الحجوزات THEN THE Booking_System SHALL يحسب الـ occupied_window شاملاً buffer_after_minutes
2. WHEN حجز جديد يتداخل مع occupied_window لحجز موجود THEN THE Booking_System SHALL يرفض الحجز الجديد
3. THE Booking_System SHALL يعتبر الحجوزات بحالة pending (غير منتهية الصلاحية) و confirmed كـ blocking

### Requirement 3: اختبار Race Condition

**User Story:** كمطور، أريد اختبارات تتحقق من منع race conditions، حتى أضمن سلامة النظام.

#### Acceptance Criteria

1. THE Booking_System SHALL يجتاز اختبار إنشاء حجوزات متزامنة لنفس الفترة الزمنية
2. WHEN يتم تشغيل اختبار race condition THEN THE Booking_System SHALL يقبل حجز واحد فقط من الحجوزات المتزامنة
3. THE Booking_System SHALL يجتاز اختبار property-based للتحقق من عدم وجود تعارضات

### Requirement 4: سلامة البيانات

**User Story:** كمدير النظام، أريد ضمان سلامة بيانات الحجوزات، حتى لا تحدث تعارضات في قاعدة البيانات.

#### Acceptance Criteria

1. THE Booking_System SHALL يستخدم database transactions لضمان atomicity
2. IF حدث خطأ أثناء إنشاء الحجز THEN THE Booking_System SHALL يتراجع عن جميع التغييرات (rollback)
3. THE Booking_System SHALL يضمن أن لا يوجد حجزان blocking متداخلان لنفس المستشار في أي وقت
