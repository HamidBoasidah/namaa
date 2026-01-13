# Requirements Document

## Introduction

هذه الوثيقة تحدد متطلبات API لعرض الملف الشخصي العام للمستشار، والذي يتضمن الشهادات والخبرات والخدمات. هذا الـ API مخصص للاستخدام من قبل التطبيق المحمول لعرض بيانات المستشارين للمستخدمين.

## Glossary

- **Consultant_Public_Profile_API**: واجهة برمجة التطبيقات لعرض الملف الشخصي العام للمستشار
- **Certificate**: شهادة المستشار (الاسم، المؤسسة، التاريخ)
- **Experience**: خبرة المستشار (الاسم فقط)
- **Service**: خدمة المستشار (الاسم، الوصف، الفئة، السعر)
- **Category**: فئة الخدمة

## Requirements

### Requirement 1: جلب الملف الشخصي العام للمستشار

**User Story:** كمستخدم للتطبيق المحمول، أريد عرض الملف الشخصي العام للمستشار، حتى أتمكن من معرفة شهاداته وخبراته وخدماته قبل حجز استشارة.

#### Acceptance Criteria

1. WHEN a user requests a consultant's public profile by consultant ID, THE Consultant_Public_Profile_API SHALL return the consultant's certificates, experiences, and services
2. WHEN returning experiences, THE Consultant_Public_Profile_API SHALL return only the experience name
3. WHEN returning services, THE Consultant_Public_Profile_API SHALL return the service title, description, category name, and price
4. WHEN returning certificates, THE Consultant_Public_Profile_API SHALL return the certificate name, issuing institution, and issue date
5. IF the consultant ID does not exist, THEN THE Consultant_Public_Profile_API SHALL return a 404 error with an appropriate message
6. IF the consultant is not active, THEN THE Consultant_Public_Profile_API SHALL return a 404 error with an appropriate message

### Requirement 2: هيكل البيانات المرجعة

**User Story:** كمطور للتطبيق المحمول، أريد الحصول على البيانات بتنسيق واضح ومنظم، حتى أتمكن من عرضها بسهولة في واجهة المستخدم.

#### Acceptance Criteria

1. THE Consultant_Public_Profile_API SHALL return data in JSON format with consistent structure
2. WHEN returning the response, THE Consultant_Public_Profile_API SHALL include separate arrays for certificates, experiences, and services
3. WHEN returning services, THE Consultant_Public_Profile_API SHALL include the category as a nested object with id and name
4. THE Consultant_Public_Profile_API SHALL return empty arrays for certificates, experiences, or services if none exist

### Requirement 3: قابلية التوسع للتقييمات (مستقبلي)

**User Story:** كمطور، أريد أن يكون الـ API قابلاً للتوسع، حتى أتمكن من إضافة التقييم وعدد التقييمات مستقبلاً.

#### Acceptance Criteria

1. THE Consultant_Public_Profile_API SHALL be designed to accommodate future addition of rating_avg and ratings_count fields
2. WHEN rating fields are added in the future, THE Consultant_Public_Profile_API SHALL return them at the consultant level, not per service
