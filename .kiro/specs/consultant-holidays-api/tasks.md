# Implementation Plan: Consultant Holidays API

## Overview

خطة تنفيذ RESTful API لإدارة إجازات المستشارين. يتم البناء على الهيكل الموجود (Service, Repository) مع إضافة Controller جديد و Policy للتحقق من الصلاحيات.

## Tasks

- [x] 1. إنشاء Policy للتحقق من الصلاحيات
  - [x] 1.1 إنشاء ملف ConsultantHolidayPolicy
    - إنشاء `app/Policies/ConsultantHolidayPolicy.php`
    - تنفيذ methods: view, create, update, delete
    - التحقق من أن المستخدم مستشار وأن الإجازة تخصه
    - _Requirements: 6.1, 6.2, 6.3_

  - [x] 1.2 تسجيل Policy في AppServiceProvider
    - إضافة mapping في Gate::policy
    - _Requirements: 6.4_

- [x] 2. تحديث Service Layer
  - [x] 2.1 إضافة methods جديدة لـ ConsultantHolidayService
    - إضافة `find(int $id)`
    - إضافة `findForConsultant(int $id, int $consultantId)`
    - إضافة `getQueryForConsultant(int $consultantId)`
    - إضافة `create(array $attributes)`
    - إضافة `update(int $id, array $attributes)`
    - إضافة `delete(int $id)`
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

- [x] 3. إنشاء Form Requests
  - [x] 3.1 إنشاء StoreConsultantHolidayRequest
    - التحقق من صيغة التاريخ YYYY-MM-DD
    - التحقق من أن التاريخ اليوم أو مستقبلي
    - _Requirements: 3.2, 3.3_

  - [x] 3.2 إنشاء UpdateConsultantHolidayRequest
    - التحقق من صيغة التاريخ
    - التحقق من عدم التكرار
    - _Requirements: 4.4_

- [x] 4. إنشاء API Controller
  - [x] 4.1 إنشاء ملف ConsultantHolidayController
    - إنشاء `app/Http/Controllers/Api/ConsultantHolidayController.php`
    - استخدام traits: ExceptionHandler, SuccessResponse
    - إضافة middleware auth:sanctum
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

  - [x] 4.2 تنفيذ method index
    - جلب الإجازات للمستشار الحالي
    - دعم الترقيم (pagination)
    - ترتيب النتائج حسب holiday_date
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 4.3 تنفيذ method show
    - جلب إجازة محددة
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 4.4 تنفيذ method store
    - إنشاء إجازة جديدة
    - تعيين consultant_id تلقائياً
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [x] 4.5 تنفيذ method update
    - تحديث إجازة موجودة
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 4.6 تنفيذ method destroy
    - حذف إجازة
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

- [x] 5. إضافة API Routes
  - [x] 5.1 إضافة routes في api.php
    - إضافة routes لـ holidays CRUD
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

- [x] 6. Checkpoint - التحقق من عمل الـ API
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. إنشاء ملف Postman Collection
  - [x] 7.1 إنشاء ملف postman/consultant-holidays.collection.json
    - إضافة requests لجميع العمليات CRUD
    - إضافة متغيرات للـ authentication token
    - إضافة أمثلة لـ request bodies
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [x] 8. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- يتم الاستفادة من ConsultantHolidayService و ConsultantHolidayRepository الموجودين
- يتم اتباع نفس نمط ConsultantWorkingHourController للـ API
- يتم اتباع نفس نمط ConsultantWorkingHourPolicy للـ Policy
- جميع الرسائل باللغة العربية
