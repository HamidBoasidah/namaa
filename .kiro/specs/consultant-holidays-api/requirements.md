# Requirements Document

## Introduction

هذه الميزة توفر RESTful API لإدارة إجازات المستشارين، بحيث يتمكن المستشار من إدارة أيام إجازاته من خلال التطبيق. تشمل العمليات: عرض جميع الإجازات، عرض إجازة محددة، إنشاء إجازة جديدة، تعديل إجازة، وحذف إجازة. يتم ضمان أن المستشار فقط هو من يستطيع إدارة إجازاته الخاصة من خلال Policy.

## Glossary

- **Consultant_Holiday_API**: واجهة برمجة التطبيقات الخاصة بإدارة إجازات المستشارين
- **Holiday**: سجل يمثل يوم إجازة محدد للمستشار
- **Consultant**: المستشار المالك للإجازات
- **User**: المستخدم المسجل في النظام والذي قد يكون مستشاراً
- **Policy**: آلية التحقق من صلاحيات المستخدم للوصول إلى الموارد
- **Holiday_Date**: تاريخ الإجازة بصيغة YYYY-MM-DD

## Requirements

### Requirement 1: عرض جميع الإجازات

**User Story:** As a consultant, I want to view all my holidays, so that I can see my complete holiday schedule.

#### Acceptance Criteria

1. WHEN a consultant requests their holidays list THEN THE Consultant_Holiday_API SHALL return all holidays belonging to that consultant
2. THE Consultant_Holiday_API SHALL order results by holiday_date ascending
3. THE Consultant_Holiday_API SHALL support pagination with configurable per_page parameter
4. IF the consultant has no holidays THEN THE Consultant_Holiday_API SHALL return an empty list with success status

### Requirement 2: عرض إجازة محددة

**User Story:** As a consultant, I want to view a specific holiday record, so that I can see its details.

#### Acceptance Criteria

1. WHEN a consultant requests a specific holiday by ID THEN THE Consultant_Holiday_API SHALL return the holiday details
2. IF the holiday does not exist THEN THE Consultant_Holiday_API SHALL return a 404 not found error
3. IF the holiday belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error

### Requirement 3: إنشاء إجازة جديدة

**User Story:** As a consultant, I want to create a new holiday, so that I can mark days when I am unavailable.

#### Acceptance Criteria

1. WHEN a consultant submits valid holiday data THEN THE Consultant_Holiday_API SHALL create a new holiday record
2. THE Consultant_Holiday_API SHALL validate that holiday_date is in YYYY-MM-DD format
3. THE Consultant_Holiday_API SHALL validate that holiday_date is today or a future date
4. IF the holiday_date already exists for this consultant THEN THE Consultant_Holiday_API SHALL reject with validation error
5. THE Consultant_Holiday_API SHALL automatically assign the consultant_id from the authenticated user
6. WHEN creation succeeds THEN THE Consultant_Holiday_API SHALL return the created record with 201 status
7. THE Consultant_Holiday_API SHALL accept an optional name field for the holiday

### Requirement 4: تعديل إجازة

**User Story:** As a consultant, I want to update an existing holiday, so that I can modify the date or name.

#### Acceptance Criteria

1. WHEN a consultant submits valid update data THEN THE Consultant_Holiday_API SHALL update the holiday record
2. IF the holiday does not exist THEN THE Consultant_Holiday_API SHALL return 404 not found error
3. IF the holiday belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error
4. THE Consultant_Holiday_API SHALL validate updated date does not duplicate another holiday
5. WHEN update succeeds THEN THE Consultant_Holiday_API SHALL return the updated record

### Requirement 5: حذف إجازة

**User Story:** As a consultant, I want to delete a holiday, so that I can remove it from my schedule.

#### Acceptance Criteria

1. WHEN a consultant requests to delete a holiday THEN THE Consultant_Holiday_API SHALL remove the record
2. IF the holiday does not exist THEN THE Consultant_Holiday_API SHALL return 404 not found error
3. IF the holiday belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error
4. WHEN deletion succeeds THEN THE Consultant_Holiday_API SHALL return success message

### Requirement 6: التحقق من الصلاحيات (Policy)

**User Story:** As a system administrator, I want to ensure consultants can only manage their own holidays, so that data integrity is maintained.

#### Acceptance Criteria

1. THE Policy SHALL verify the authenticated user is a consultant
2. THE Policy SHALL verify the holiday belongs to the authenticated consultant
3. IF authorization fails THEN THE Policy SHALL return 403 forbidden response
4. THE Policy SHALL be applied to view, create, update, and delete operations

### Requirement 7: ملف Postman للاختبار

**User Story:** As a developer, I want a Postman collection file, so that I can test all API endpoints easily.

#### Acceptance Criteria

1. THE Postman_Collection SHALL include requests for all CRUD operations
2. THE Postman_Collection SHALL include authentication token variable
3. THE Postman_Collection SHALL include example request bodies
4. THE Postman_Collection SHALL be saved in the postman directory
