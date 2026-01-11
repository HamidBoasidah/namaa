# Implementation Plan: Consultant Working Hours API

## Overview

خطة تنفيذ RESTful API لإدارة ساعات عمل المستشارين. يتم البناء على الهيكل الموجود (Service, Repository) مع إضافة Controller جديد و Policy للتحقق من الصلاحيات.

## Tasks

- [x] 1. إنشاء Policy للتحقق من الصلاحيات
  - [x] 1.1 إنشاء ملف ConsultantWorkingHourPolicy
    - إنشاء `app/Policies/ConsultantWorkingHourPolicy.php`
    - تنفيذ methods: view, create, update, delete, activate, deactivate
    - التحقق من أن المستخدم مستشار وأن ساعة العمل تخصه
    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 1.2 تسجيل Policy في AuthServiceProvider
    - إضافة mapping في `$policies` array
    - _Requirements: 7.4_

- [x] 2. تحديث Service Layer
  - [x] 2.1 إضافة methods جديدة لـ ConsultantWorkingHourService
    - إضافة `findForConsultant(int $id, int $consultantId)`
    - إضافة `getQueryForConsultant(int $consultantId)`
    - _Requirements: 1.1, 2.1_

- [x] 3. إنشاء API Controller
  - [x] 3.1 إنشاء ملف ConsultantWorkingHourController
    - إنشاء `app/Http/Controllers/Api/ConsultantWorkingHourController.php`
    - استخدام traits: ExceptionHandler, SuccessResponse, CanFilter
    - إضافة middleware auth:sanctum
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

  - [x] 3.2 تنفيذ method index
    - جلب ساعات العمل للمستشار الحالي
    - دعم الترقيم (pagination)
    - ترتيب النتائج حسب day_of_week ثم start_time
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 3.3 تنفيذ method show
    - جلب ساعة عمل محددة
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 3.4 تنفيذ method store
    - إنشاء ساعة عمل جديدة
    - تعيين consultant_id تلقائياً
    - استخدام StoreConsultantWorkingHourRequest للتحقق
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [x] 3.5 تنفيذ method update
    - تحديث ساعة عمل موجودة
    - التحقق من الصلاحيات عبر Policy
    - استخدام UpdateConsultantWorkingHourRequest للتحقق
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 3.6 تنفيذ method destroy
    - حذف ساعة عمل
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x] 3.7 تنفيذ methods activate و deactivate
    - تفعيل/تعطيل ساعة عمل
    - التحقق من الصلاحيات عبر Policy
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 4. تحديث Form Requests
  - [x] 4.1 تحديث StoreConsultantWorkingHourRequest
    - إزالة consultant_id من required (سيتم تعيينه تلقائياً)
    - التأكد من validation rules
    - _Requirements: 3.2, 3.3, 3.4, 3.5_

  - [x] 4.2 تحديث UpdateConsultantWorkingHourRequest
    - إزالة consultant_id من required
    - التأكد من validation rules
    - _Requirements: 4.4_

- [x] 5. إضافة API Routes
  - [x] 5.1 إضافة routes في api.php
    - إضافة resource routes لـ working-hours
    - إضافة routes لـ activate و deactivate
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1, 6.1, 6.2_

- [x] 6. Checkpoint - التحقق من عمل الـ API
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. إنشاء ملف Postman Collection
  - [x] 7.1 إنشاء ملف postman/consultant-working-hours.collection.json
    - إضافة requests لجميع العمليات CRUD
    - إضافة requests لـ activate و deactivate
    - إضافة متغيرات للـ authentication token
    - إضافة أمثلة لـ request bodies
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 8. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- يتم الاستفادة من ConsultantWorkingHourService و ConsultantWorkingHourRepository الموجودين
- يتم اتباع نفس نمط AddressController للـ API
- يتم اتباع نفس نمط ConsultantExperiencePolicy للـ Policy
- جميع الرسائل باللغة العربية
