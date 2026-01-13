# Implementation Plan: Consultant Public Profile API

## Overview

خطة تنفيذ API لعرض الملف الشخصي العام للمستشار (الشهادات، الخبرات، الخدمات) في التطبيق المحمول.

## Tasks

- [x] 1. إنشاء DTO للملف الشخصي العام
  - [x] 1.1 إنشاء `ConsultantPublicProfileDTO` في `app/DTOs/`
    - إنشاء method `fromModel` لتحويل بيانات المستشار
    - تنسيق الخبرات لإرجاع الاسم فقط
    - تنسيق الخدمات لإرجاع (title, description, price, category)
    - تنسيق الشهادات
    - _Requirements: 1.2, 1.3, 1.4, 2.2, 2.3_

- [x] 2. تحديث Service Layer
  - [x] 2.1 إضافة method `getPublicProfile` في `ConsultantService`
    - جلب المستشار مع العلاقات (certificates, experiences, service.category)
    - التحقق من وجود المستشار ونشاطه
    - إرجاع البيانات المنسقة
    - _Requirements: 1.1, 1.5, 1.6, 2.4_

- [x] 3. تحديث Controller Layer
  - [x] 3.1 إضافة method `profile` في `Mobile/ConsultantController`
    - استقبال `consultantId` من الـ URL
    - استدعاء `ConsultantService::getPublicProfile`
    - إرجاع الـ response بالتنسيق المطلوب
    - _Requirements: 1.1, 2.1_

- [x] 4. إضافة Route
  - [x] 4.1 إضافة route جديد في `routes/api.php`
    - `GET /api/mobile/consultants/{consultantId}/profile`
    - _Requirements: 1.1_

- [x] 5. Checkpoint - التحقق من عمل الـ API
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 6. كتابة الاختبارات
  - [ ]* 6.1 كتابة Unit Tests للـ Service
    - **Property 1: Response Structure Validation**
    - **Validates: Requirements 1.2, 1.3, 1.4, 2.2, 2.3**
  
  - [ ]* 6.2 كتابة Unit Tests لمعالجة الأخطاء
    - **Property 3: Non-Existent Consultant Error**
    - **Property 4: Inactive Consultant Error**
    - **Validates: Requirements 1.5, 1.6**
  
  - [ ]* 6.3 كتابة Unit Tests للبيانات الفارغة
    - **Property 2: Empty Data Handling**
    - **Validates: Requirements 2.4**

- [ ] 7. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- الـ API سيكون public (لا يحتاج authentication)
- الـ model الحالي للـ Certificate لا يحتوي على name/issuing_institution - سيتم إرجاع الحقول الموجودة
- التقييمات (rating_avg, ratings_count) موجودة في model الـ Consultant ويمكن إضافتها لاحقاً
