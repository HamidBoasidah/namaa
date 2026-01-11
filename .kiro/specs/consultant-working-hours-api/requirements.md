# Requirements Document

## Introduction

هذه الميزة توفر RESTful API لإدارة ساعات عمل المستشارين، بحيث يتمكن المستشار من إدارة جدول ساعات عمله الأسبوعي من خلال التطبيق. تشمل العمليات: عرض جميع ساعات العمل، عرض ساعة عمل محددة، إنشاء ساعة عمل جديدة، تعديل ساعة عمل، وحذف ساعة عمل. يتم ضمان أن المستشار فقط هو من يستطيع إدارة ساعات عمله الخاصة من خلال Policy.

## Glossary

- **Consultant_Working_Hour_API**: واجهة برمجة التطبيقات الخاصة بإدارة ساعات عمل المستشارين
- **Working_Hour**: سجل يمثل فترة عمل محددة في يوم معين من الأسبوع
- **Consultant**: المستشار المالك لساعات العمل
- **User**: المستخدم المسجل في النظام والذي قد يكون مستشاراً
- **Policy**: آلية التحقق من صلاحيات المستخدم للوصول إلى الموارد
- **Day_Of_Week**: رقم يمثل يوم الأسبوع (0=الأحد، 6=السبت)

## Requirements

### Requirement 1: عرض جميع ساعات العمل

**User Story:** As a consultant, I want to view all my working hours, so that I can see my complete weekly schedule.

#### Acceptance Criteria

1. WHEN a consultant requests their working hours list THEN THE Consultant_Working_Hour_API SHALL return all working hours belonging to that consultant
2. THE Consultant_Working_Hour_API SHALL order results by day_of_week then by start_time
3. THE Consultant_Working_Hour_API SHALL support pagination with configurable per_page parameter
4. IF the consultant has no working hours THEN THE Consultant_Working_Hour_API SHALL return an empty list with success status

### Requirement 2: عرض ساعة عمل محددة

**User Story:** As a consultant, I want to view a specific working hour record, so that I can see its details.

#### Acceptance Criteria

1. WHEN a consultant requests a specific working hour by ID THEN THE Consultant_Working_Hour_API SHALL return the working hour details
2. IF the working hour does not exist THEN THE Consultant_Working_Hour_API SHALL return a 404 not found error
3. IF the working hour belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error

### Requirement 3: إنشاء ساعة عمل جديدة

**User Story:** As a consultant, I want to create a new working hour, so that I can add availability to my schedule.

#### Acceptance Criteria

1. WHEN a consultant submits valid working hour data THEN THE Consultant_Working_Hour_API SHALL create a new working hour record
2. THE Consultant_Working_Hour_API SHALL validate that start_time is before end_time
3. THE Consultant_Working_Hour_API SHALL validate day_of_week is between 0 and 6
4. THE Consultant_Working_Hour_API SHALL validate time format as HH:MM
5. IF the new working hour overlaps with an existing one on the same day THEN THE Consultant_Working_Hour_API SHALL reject with validation error
6. THE Consultant_Working_Hour_API SHALL automatically assign the consultant_id from the authenticated user
7. WHEN creation succeeds THEN THE Consultant_Working_Hour_API SHALL return the created record with 201 status

### Requirement 4: تعديل ساعة عمل

**User Story:** As a consultant, I want to update an existing working hour, so that I can modify my availability.

#### Acceptance Criteria

1. WHEN a consultant submits valid update data THEN THE Consultant_Working_Hour_API SHALL update the working hour record
2. IF the working hour does not exist THEN THE Consultant_Working_Hour_API SHALL return 404 not found error
3. IF the working hour belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error
4. THE Consultant_Working_Hour_API SHALL validate updated times do not overlap with other working hours
5. WHEN update succeeds THEN THE Consultant_Working_Hour_API SHALL return the updated record

### Requirement 5: حذف ساعة عمل

**User Story:** As a consultant, I want to delete a working hour, so that I can remove availability from my schedule.

#### Acceptance Criteria

1. WHEN a consultant requests to delete a working hour THEN THE Consultant_Working_Hour_API SHALL remove the record
2. IF the working hour does not exist THEN THE Consultant_Working_Hour_API SHALL return 404 not found error
3. IF the working hour belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error
4. WHEN deletion succeeds THEN THE Consultant_Working_Hour_API SHALL return success message

### Requirement 6: تفعيل/تعطيل ساعة عمل

**User Story:** As a consultant, I want to activate or deactivate a working hour, so that I can temporarily disable availability without deleting it.

#### Acceptance Criteria

1. WHEN a consultant requests to activate a working hour THEN THE Consultant_Working_Hour_API SHALL set is_active to true
2. WHEN a consultant requests to deactivate a working hour THEN THE Consultant_Working_Hour_API SHALL set is_active to false
3. IF the working hour belongs to another consultant THEN THE Policy SHALL deny access with 403 forbidden error
4. IF activating would cause overlap with another active working hour THEN THE Consultant_Working_Hour_API SHALL reject with validation error

### Requirement 7: التحقق من الصلاحيات (Policy)

**User Story:** As a system administrator, I want to ensure consultants can only manage their own working hours, so that data integrity is maintained.

#### Acceptance Criteria

1. THE Policy SHALL verify the authenticated user is a consultant
2. THE Policy SHALL verify the working hour belongs to the authenticated consultant
3. IF authorization fails THEN THE Policy SHALL return 403 forbidden response
4. THE Policy SHALL be applied to view, create, update, delete, activate, and deactivate operations

### Requirement 8: ملف Postman للاختبار

**User Story:** As a developer, I want a Postman collection file, so that I can test all API endpoints easily.

#### Acceptance Criteria

1. THE Postman_Collection SHALL include requests for all CRUD operations
2. THE Postman_Collection SHALL include requests for activate and deactivate operations
3. THE Postman_Collection SHALL include authentication token variable
4. THE Postman_Collection SHALL include example request bodies
5. THE Postman_Collection SHALL be saved in the postman directory
