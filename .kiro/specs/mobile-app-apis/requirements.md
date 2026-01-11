# Requirements Document

## Introduction

هذا المستند يحدد متطلبات APIs لتطبيق الجوال التي تتيح للمستخدمين استعراض الفئات والمستشارين. تشمل هذه الـ APIs:
- جلب جميع الفئات
- جلب الفئات حسب نوع الاستشارة مع عدد المستشارين
- جلب المستشارين حسب الفئة
- عرض المستشارين مع خيارات الترتيب المتعددة

## Glossary

- **Category**: الفئة - تصنيف للخدمات الاستشارية مرتبط بنوع استشارة معين
- **ConsultationType**: نوع الاستشارة - التصنيف الرئيسي للاستشارات
- **Consultant**: المستشار - المستخدم الذي يقدم خدمات استشارية
- **ConsultantService**: خدمة المستشار - الخدمة التي يقدمها المستشار ضمن فئة معينة
- **Mobile_API**: واجهة برمجة التطبيقات للجوال - نقاط النهاية المخصصة لتطبيق الجوال
- **Active_Consultant**: المستشار النشط - المستشار الذي حالته is_active = true
- **Service_Categories**: فئات الخدمات - الفئات المرتبطة بخدمات المستشار

## Requirements

### Requirement 1: جلب جميع الفئات

**User Story:** As a mobile app user, I want to view all categories, so that I can browse available consultation categories.

#### Acceptance Criteria

1. WHEN a user requests all categories, THE Mobile_API SHALL return a list of all active categories
2. THE Mobile_API SHALL return only the following fields for each category: id, name, icon_url
3. WHEN categories are returned, THE Mobile_API SHALL include only categories where is_active equals true
4. IF no active categories exist, THEN THE Mobile_API SHALL return an empty list with a success response

### Requirement 2: جلب الفئات حسب نوع الاستشارة

**User Story:** As a mobile app user, I want to view categories for a specific consultation type with consultant counts, so that I can see which categories have available consultants.

#### Acceptance Criteria

1. WHEN a user provides a consultation_type_id, THE Mobile_API SHALL return categories belonging to that consultation type
2. THE Mobile_API SHALL return the following fields for each category: id, name, icon_url, consultants_count
3. THE Mobile_API SHALL calculate consultants_count as the number of active consultants who have services in that category
4. WHEN calculating consultants_count, THE Mobile_API SHALL count only consultants where is_active equals true
5. THE Mobile_API SHALL return only categories where is_active equals true
6. IF the consultation_type_id does not exist, THEN THE Mobile_API SHALL return a 404 error response
7. IF no categories exist for the consultation type, THEN THE Mobile_API SHALL return an empty list with a success response

### Requirement 3: جلب المستشارين حسب الفئة

**User Story:** As a mobile app user, I want to view consultants who offer services in a specific category, so that I can find relevant consultants.

#### Acceptance Criteria

1. WHEN a user provides a category_id, THE Mobile_API SHALL return consultants who have services in that category
2. THE Mobile_API SHALL return the following fields for each consultant: id, first_name, last_name, avatar, rating_avg, ratings_count, service_categories
3. THE Mobile_API SHALL return service_categories as a unique list of category names for all services of the consultant
4. WHEN returning consultants, THE Mobile_API SHALL include only consultants where is_active equals true
5. IF the category_id does not exist, THEN THE Mobile_API SHALL return a 404 error response
6. IF no consultants have services in the category, THEN THE Mobile_API SHALL return an empty list with a success response
7. THE Mobile_API SHALL support pagination with configurable per_page parameter

### Requirement 4: عرض المستشارين مع خيارات الترتيب

**User Story:** As a mobile app user, I want to view and sort consultants by different criteria, so that I can find the best consultant for my needs.

#### Acceptance Criteria

1. WHEN a user requests consultants list, THE Mobile_API SHALL return a paginated list of active consultants
2. THE Mobile_API SHALL return the following fields for each consultant: id, first_name, last_name, avatar, rating_avg, ratings_count, service_categories
3. THE Mobile_API SHALL return service_categories as a unique list of category names for all services of the consultant
4. WHEN sort_by parameter equals "experience", THE Mobile_API SHALL order consultants by years_of_experience descending
5. WHEN sort_by parameter equals "rating", THE Mobile_API SHALL order consultants by rating_avg descending
6. WHEN sort_by parameter equals "reviews", THE Mobile_API SHALL order consultants by ratings_count descending
7. WHEN no sort_by parameter is provided, THE Mobile_API SHALL return consultants in default order (latest first)
8. THE Mobile_API SHALL support pagination with configurable per_page parameter
9. WHEN returning consultants, THE Mobile_API SHALL include only consultants where is_active equals true
10. IF no active consultants exist, THEN THE Mobile_API SHALL return an empty list with a success response

### Requirement 5: تنظيم الـ Controllers

**User Story:** As a developer, I want APIs organized in separate controllers by model, so that the codebase follows best practices and is maintainable.

#### Acceptance Criteria

1. THE Mobile_API SHALL organize category-related endpoints in a dedicated CategoryController
2. THE Mobile_API SHALL organize consultant-related endpoints in a dedicated ConsultantController
3. THE Mobile_API SHALL follow the existing project structure pattern using Services, Repositories, and DTOs
4. THE Mobile_API SHALL use the existing SuccessResponse trait for consistent response formatting
5. THE Mobile_API SHALL use the existing CanFilter trait for filtering and pagination support
