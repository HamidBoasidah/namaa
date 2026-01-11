# Design Document: Consultant Working Hours API

## Overview

هذا التصميم يوفر RESTful API لإدارة ساعات عمل المستشارين باستخدام Laravel. يتبع التصميم نمط Repository-Service-Controller المستخدم في المشروع الحالي، مع إضافة Policy للتحقق من الصلاحيات.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Layer                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantWorkingHourController (API)                │   │
│  │     - index, show, store, update, destroy                │   │
│  │     - activate, deactivate                               │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Authorization Layer                          │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantWorkingHourPolicy                          │   │
│  │     - view, create, update, delete                       │   │
│  │     - activate, deactivate                               │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Service Layer                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantWorkingHourService (existing)              │   │
│  │     - allForConsultant, create, update, delete           │   │
│  │     - activate, deactivate                               │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Repository Layer                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantWorkingHourRepository (existing)           │   │
│  │     - forConsultant, hasOverlap, findOrFail              │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. ConsultantWorkingHourController (API)

```php
namespace App\Http\Controllers\Api;

class ConsultantWorkingHourController extends Controller
{
    use ExceptionHandler, SuccessResponse, CanFilter;

    // GET /api/consultant/working-hours
    public function index(Request $request, ConsultantWorkingHourService $service): JsonResponse;
    
    // GET /api/consultant/working-hours/{id}
    public function show(Request $request, ConsultantWorkingHourService $service, int $id): JsonResponse;
    
    // POST /api/consultant/working-hours
    public function store(StoreConsultantWorkingHourRequest $request, ConsultantWorkingHourService $service): JsonResponse;
    
    // PUT /api/consultant/working-hours/{id}
    public function update(UpdateConsultantWorkingHourRequest $request, ConsultantWorkingHourService $service, int $id): JsonResponse;
    
    // DELETE /api/consultant/working-hours/{id}
    public function destroy(Request $request, ConsultantWorkingHourService $service, int $id): JsonResponse;
    
    // POST /api/consultant/working-hours/{id}/activate
    public function activate(Request $request, ConsultantWorkingHourService $service, int $id): JsonResponse;
    
    // POST /api/consultant/working-hours/{id}/deactivate
    public function deactivate(Request $request, ConsultantWorkingHourService $service, int $id): JsonResponse;
}
```

### 2. ConsultantWorkingHourPolicy

```php
namespace App\Policies;

class ConsultantWorkingHourPolicy
{
    // Get consultant for authenticated user
    protected function getConsultant(User $user): ?Consultant;
    
    // Check if user can view working hour
    public function view(User $user, ConsultantWorkingHour $workingHour): bool;
    
    // Check if user can create working hours
    public function create(User $user): bool;
    
    // Check if user can update working hour
    public function update(User $user, ConsultantWorkingHour $workingHour): bool;
    
    // Check if user can delete working hour
    public function delete(User $user, ConsultantWorkingHour $workingHour): bool;
    
    // Check if user can activate working hour
    public function activate(User $user, ConsultantWorkingHour $workingHour): bool;
    
    // Check if user can deactivate working hour
    public function deactivate(User $user, ConsultantWorkingHour $workingHour): bool;
}
```

### 3. API Routes

```php
// routes/api.php
Route::middleware('auth:sanctum')->prefix('consultant')->group(function () {
    Route::apiResource('working-hours', ConsultantWorkingHourController::class);
    Route::post('working-hours/{id}/activate', [ConsultantWorkingHourController::class, 'activate']);
    Route::post('working-hours/{id}/deactivate', [ConsultantWorkingHourController::class, 'deactivate']);
});
```

### 4. Service Layer Extensions

سيتم إضافة methods جديدة للـ ConsultantWorkingHourService:

```php
// Find working hour for specific consultant
public function findForConsultant(int $id, int $consultantId): ConsultantWorkingHour;

// Get query builder for consultant's working hours
public function getQueryForConsultant(int $consultantId): Builder;
```

## Data Models

### ConsultantWorkingHour (Existing)

```
┌─────────────────────────────────────┐
│     consultant_working_hours        │
├─────────────────────────────────────┤
│ id: bigint (PK)                     │
│ consultant_id: bigint (FK)          │
│ day_of_week: integer (0-6)          │
│ start_time: string (HH:MM)          │
│ end_time: string (HH:MM)            │
│ is_active: boolean                  │
│ created_at: timestamp               │
│ updated_at: timestamp               │
└─────────────────────────────────────┘
```

### API Response Format

```json
{
    "success": true,
    "message": "تم جلب ساعات العمل بنجاح",
    "status_code": 200,
    "data": [
        {
            "id": 1,
            "consultant_id": 5,
            "day_of_week": 0,
            "day_name": "الأحد",
            "start_time": "09:00",
            "end_time": "12:00",
            "is_active": true
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 5,
        "last_page": 1
    }
}
```



## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Ownership Enforcement

*For any* working hour record and any authenticated consultant, if the working hour does not belong to that consultant, then all operations (view, update, delete, activate, deactivate) SHALL return 403 forbidden.

**Validates: Requirements 2.3, 4.3, 5.3, 6.3, 7.2**

### Property 2: List Returns Only Consultant's Records

*For any* authenticated consultant, when requesting the working hours list, all returned records SHALL have consultant_id matching the authenticated consultant's ID.

**Validates: Requirements 1.1**

### Property 3: Results Ordering

*For any* list of working hours returned by the API, the records SHALL be ordered such that for any two consecutive records (a, b), either a.day_of_week < b.day_of_week, or (a.day_of_week == b.day_of_week AND a.start_time <= b.start_time).

**Validates: Requirements 1.2**

### Property 4: Creation Assigns Correct Consultant

*For any* valid working hour creation request, the created record SHALL have consultant_id equal to the authenticated user's consultant ID, regardless of any consultant_id provided in the request body.

**Validates: Requirements 3.1, 3.6**

### Property 5: Time Validation - Start Before End

*For any* working hour creation or update request where start_time >= end_time, the API SHALL reject the request with a validation error.

**Validates: Requirements 3.2**

### Property 6: Day of Week Validation

*For any* working hour creation or update request where day_of_week is not in the range [0, 6], the API SHALL reject the request with a validation error.

**Validates: Requirements 3.3**

### Property 7: Time Format Validation

*For any* working hour creation or update request where start_time or end_time does not match the HH:MM format, the API SHALL reject the request with a validation error.

**Validates: Requirements 3.4**

### Property 8: Overlap Prevention

*For any* consultant and any day_of_week, if two working hour records exist (or would exist after an operation), and their time ranges overlap (new_start < existing_end AND new_end > existing_start), then the operation SHALL be rejected with a validation error.

**Validates: Requirements 3.5, 4.4, 6.4**

### Property 9: Update Modifies Record

*For any* valid update request to a working hour owned by the authenticated consultant, the record SHALL be modified to reflect the new values.

**Validates: Requirements 4.1**

### Property 10: Delete Removes Record

*For any* valid delete request to a working hour owned by the authenticated consultant, the record SHALL no longer exist in the database after the operation.

**Validates: Requirements 5.1**

### Property 11: Activate Sets Active True

*For any* working hour with is_active=false, when the activate operation is performed, the record SHALL have is_active=true.

**Validates: Requirements 6.1**

### Property 12: Deactivate Sets Active False

*For any* working hour with is_active=true, when the deactivate operation is performed, the record SHALL have is_active=false.

**Validates: Requirements 6.2**

### Property 13: Non-Consultant Access Denied

*For any* authenticated user who is not a consultant (user_type != 'consultant' or has no consultant record), all working hour operations SHALL return 403 forbidden.

**Validates: Requirements 7.1**

## Error Handling

### HTTP Status Codes

| Status Code | Scenario |
|-------------|----------|
| 200 | Successful GET, PUT, DELETE operations |
| 201 | Successful POST (creation) |
| 400 | Invalid request format |
| 401 | Unauthenticated request |
| 403 | Unauthorized access (Policy denial) |
| 404 | Resource not found |
| 422 | Validation error (overlap, invalid times, etc.) |

### Error Response Format

```json
{
    "success": false,
    "message": "رسالة الخطأ",
    "status_code": 422,
    "errors": {
        "end_time": ["يوجد تداخل مع فترة عمل أخرى في نفس اليوم لهذا المستشار."]
    }
}
```

### Validation Error Messages (Arabic)

- `يوجد تداخل مع فترة عمل أخرى في نفس اليوم لهذا المستشار.` - Overlap error
- `وقت النهاية يجب أن يكون بعد وقت البداية.` - End time before start time
- `يوم الأسبوع يجب أن يكون بين 0 و 6.` - Invalid day of week
- `صيغة الوقت غير صحيحة.` - Invalid time format
- `ساعة العمل المطلوبة غير موجودة.` - Working hour not found
- `غير مصرح لك بالوصول لهذا المورد.` - Unauthorized access

## Testing Strategy

### Unit Tests

Unit tests will focus on:
- Policy authorization logic
- Service layer business logic
- Validation rules in Form Requests

### Property-Based Tests

Property-based tests will be implemented using **PHPUnit with custom data providers** to generate random test cases. Each property from the Correctness Properties section will have a corresponding test.

**Configuration:**
- Minimum 100 iterations per property test
- Each test tagged with: **Feature: consultant-working-hours-api, Property {number}: {property_text}**

### Test Categories

1. **Authorization Tests** (Properties 1, 13)
   - Test ownership verification
   - Test non-consultant access denial

2. **CRUD Operation Tests** (Properties 2, 3, 4, 9, 10)
   - Test list filtering and ordering
   - Test creation with correct consultant assignment
   - Test update and delete operations

3. **Validation Tests** (Properties 5, 6, 7, 8)
   - Test time validation
   - Test day of week validation
   - Test overlap prevention

4. **State Change Tests** (Properties 11, 12)
   - Test activate/deactivate operations

### Postman Collection

A Postman collection will be created at `postman/consultant-working-hours.collection.json` containing:
- All CRUD endpoints
- Activate/Deactivate endpoints
- Example request bodies
- Environment variables for authentication token
