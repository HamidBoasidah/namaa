# Implementation Plan: Mobile App APIs

## Overview

خطة تنفيذ APIs تطبيق الجوال لاستعراض الفئات والمستشارين. يتبع التنفيذ النمط الموجود في المشروع باستخدام Controllers, Services, Repositories, و DTOs.

## Tasks

- [x] 1. إنشاء DTOs للجوال
  - [x] 1.1 إنشاء CategoryMobileDTO
    - إنشاء ملف `app/DTOs/CategoryMobileDTO.php`
    - تضمين الحقول: id, name, icon_url, consultants_count
    - إنشاء دوال fromModel(), toArray(), toArrayWithCount()
    - _Requirements: 1.2, 2.2_
  - [x] 1.2 إنشاء ConsultantMobileDTO
    - إنشاء ملف `app/DTOs/ConsultantMobileDTO.php`
    - تضمين الحقول: id, first_name, last_name, avatar, rating_avg, ratings_count, service_categories
    - إنشاء دوال fromModel(), toArray()
    - _Requirements: 3.2, 4.2_

- [x] 2. توسيع Services بالدوال الجديدة
  - [x] 2.1 إضافة دوال CategoryService
    - إضافة getActiveForMobile() لجلب الفئات النشطة
    - إضافة getByConsultationTypeWithConsultantsCount() لجلب الفئات مع عدد المستشارين
    - _Requirements: 1.1, 2.1, 2.3_
  - [ ]* 2.2 كتابة property test لحساب عدد المستشارين
    - **Property 3: Consultants Count Calculation**
    - **Validates: Requirements 2.3, 2.4**
  - [x] 2.3 إضافة دوال ConsultantService
    - إضافة getByCategory() لجلب المستشارين حسب الفئة
    - إضافة getForMobile() لجلب المستشارين مع الترتيب
    - إضافة getServiceCategories() لجلب فئات خدمات المستشار
    - _Requirements: 3.1, 4.1, 4.4, 4.5, 4.6, 4.7_
  - [ ]* 2.4 كتابة property test للترتيب
    - **Property 8: Consultant Sorting**
    - **Validates: Requirements 4.4, 4.5, 4.6, 4.7**

- [x] 3. Checkpoint - التحقق من Services
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. إنشاء Controllers للجوال
  - [x] 4.1 إنشاء CategoryController للجوال
    - إنشاء ملف `app/Http/Controllers/Api/Mobile/CategoryController.php`
    - إنشاء index() لجلب جميع الفئات
    - إنشاء byConsultationType() لجلب الفئات حسب نوع الاستشارة
    - استخدام SuccessResponse trait
    - _Requirements: 1.1, 2.1, 2.6_
  - [ ]* 4.2 كتابة property test لفلترة الفئات النشطة
    - **Property 1: Active Categories Filter**
    - **Validates: Requirements 1.1, 1.3, 2.5**
  - [x] 4.3 إنشاء ConsultantController للجوال
    - إنشاء ملف `app/Http/Controllers/Api/Mobile/ConsultantController.php`
    - إنشاء byCategory() لجلب المستشارين حسب الفئة
    - إنشاء index() لعرض المستشارين مع الترتيب
    - استخدام SuccessResponse و CanFilter traits
    - _Requirements: 3.1, 4.1, 3.5_
  - [ ]* 4.4 كتابة property test لفلترة المستشارين النشطين
    - **Property 7: Active Consultants Filter**
    - **Validates: Requirements 3.4, 4.1, 4.9**

- [x] 5. إعداد Routes
  - [x] 5.1 إضافة routes للجوال
    - إضافة routes في `routes/api.php` تحت prefix `mobile`
    - تسجيل جميع endpoints المطلوبة
    - _Requirements: 5.1, 5.2_

- [x] 6. Checkpoint - التحقق من التكامل
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. اختبارات إضافية
  - [ ]* 7.1 كتابة property test لهيكل استجابة الفئات
    - **Property 2: Category Response Structure**
    - **Validates: Requirements 1.2, 2.2**
  - [ ]* 7.2 كتابة property test لفئات الخدمات الفريدة
    - **Property 6: Unique Service Categories**
    - **Validates: Requirements 3.3, 4.3**
  - [ ]* 7.3 كتابة property test للـ pagination
    - **Property 9: Pagination Consistency**
    - **Validates: Requirements 3.7, 4.8**

- [x] 8. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- يتم استخدام PHPUnit مع Faker للاختبارات
