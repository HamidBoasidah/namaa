# Design Document: Consultant Holidays API

## Overview

هذا التصميم يوفر RESTful API لإدارة إجازات المستشارين باستخدام Laravel. يتبع التصميم نمط Repository-Service-Controller المستخدم في المشروع الحالي، مع إضافة Policy للتحقق من الصلاحيات.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Layer                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantHolidayController (API)                    │   │
│  │     - index, show, store, update, destroy                │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Authorization Layer                          │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantHolidayPolicy                              │   │
│  │     - view, create, update, delete                       │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Service Layer                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantHolidayService (existing + extensions)     │   │
│  │     - allForConsultant, create, update, delete, find     │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Repository Layer                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │     ConsultantHolidayRepository (existing)               │   │
│  │     - forConsultant, allForConsultant, findOrFail        │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. ConsultantHolidayController (API)

```php
namespace App\Http\Controllers\Api;

class ConsultantHolidayController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    // GET /api/consultant/holidays
    public function index(Request $request, ConsultantHolidayService $service): JsonResponse;
    
    // GET /api/consultant/holidays/{id}
    public function show(Request $request, ConsultantHolidayService $service, int $id): JsonResponse;
    
    // POST /api/consultant/holidays
    public function store(StoreConsultantHolidayRequest $request, ConsultantHolidayService $service): JsonResponse;
    
    // PUT /api/consultant/holidays/{id}
    public function update(UpdateConsultantHolidayRequest $request, ConsultantHolidayService $service, int $id): JsonResponse;
    
    // DELETE /api/consultant/holidays/{id}
    public function destroy(Request $request, ConsultantHolidayService $service, int $id): JsonResponse;
}
```

### 2. ConsultantHolidayPolicy

```php
namespace App\Policies;

class ConsultantHolidayPolicy
{
    // Get consultant for authenticated user
    protected function getConsultant(User $user): ?Consultant;
    
    // Check if user can view holiday
    public function view(User $user, ConsultantHoliday $holiday): bool;
    
    // Check if user can create holidays
    public function create(User $user): bool;
    
    // Check if user can update holiday
    public function update(User $user, ConsultantHoliday $holiday): bool;
    
    // Check if user can delete holiday
    public function delete(User $user, ConsultantHoliday $holiday): bool;
}
```

### 3. API Routes

```php
// routes/api.php
Route::prefix('consultant/holidays')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ConsultantHolidayController::class, 'index']);
    Route::post('/', [ConsultantHolidayController::class, 'store']);
    Route::get('/{id}', [ConsultantHolidayController::class, 'show']);
    Route::put('/{id}', [ConsultantHolidayController::class, 'update']);
    Route::delete('/{id}', [ConsultantHolidayController::class, 'destroy']);
});
```

### 4. Service Layer Extensions

سيتم إضافة methods جديدة للـ ConsultantHolidayService:

```php
// Find holiday for specific consultant
public function findForConsultant(int $id, int $consultantId): ConsultantHoliday;

// Find holiday by ID
public function find(int $id): ConsultantHoliday;

// Get query builder for consultant's holidays
public function getQueryForConsultant(int $consultantId): Builder;

// Create single holiday
public function create(array $attributes): ConsultantHoliday;

// Update holiday
public function update(int $id, array $attributes): ConsultantHoliday;

// Delete holiday
public function delete(int $id): bool;
```

## Data Models

### ConsultantHoliday (Existing)

```
┌─────────────────────────────────────┐
│       consultant_holidays           │
├─────────────────────────────────────┤
│ id: bigint (PK)                     │
│ consultant_id: bigint (FK)          │
│ holiday_date: date (YYYY-MM-DD)     │
│ name: string (nullable)             │
│ created_at: timestamp               │
│ updated_at: timestamp               │
│ deleted_at: timestamp (soft delete) │
└─────────────────────────────────────┘
```

### API Response Format

```json
{
    "success": true,
    "message": "تم جلب الإجازات بنجاح",
    "status_code": 200,
    "data": [
        {
            "id": 1,
            "consultant_id": 5,
            "holiday_date": "2026-01-15",
            "name": "إجازة شخصية",
            "created_at": "2026-01-10T10:00:00.000Z",
            "updated_at": "2026-01-10T10:00:00.000Z"
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

*For any* holiday record and any authenticated consultant, if the holiday does not belong to that consultant, then all operations (view, update, delete) SHALL return 403 forbidden.

**Validates: Requirements 2.3, 4.3, 5.3, 6.2**

### Property 2: List Returns Only Consultant's Records

*For any* authenticated consultant, when requesting the holidays list, all returned records SHALL have consultant_id matching the authenticated consultant's ID.

**Validates: Requirements 1.1**

### Property 3: Results Ordering by Date

*For any* list of holidays returned by the API, the records SHALL be ordered such that for any two consecutive records (a, b), a.holiday_date <= b.holiday_date.

**Validates: Requirements 1.2**

### Property 4: Creation Assigns Correct Consultant

*For any* valid holiday creation request, the created record SHALL have consultant_id equal to the authenticated user's consultant ID, regardless of any consultant_id provided in the request body.

**Validates: Requirements 3.1, 3.5**

### Property 5: Date Format Validation

*For any* holiday creation or update request where holiday_date does not match the YYYY-MM-DD format, the API SHALL reject the request with a validation error.

**Validates: Requirements 3.2**

### Property 6: Future Date Validation

*For any* holiday creation or update request where holiday_date is in the past (before today), the API SHALL reject the request with a validation error.

**Validates: Requirements 3.3**

### Property 7: Duplicate Date Prevention

*For any* consultant, if a holiday with a specific date already exists, attempting to create or update another holiday with the same date SHALL be rejected with a validation error.

**Validates: Requirements 3.4, 4.4**

### Property 8: Update Modifies Record

*For any* valid update request to a holiday owned by the authenticated consultant, the record SHALL be modified to reflect the new values.

**Validates: Requirements 4.1**

### Property 9: Delete Removes Record

*For any* valid delete request to a holiday owned by the authenticated consultant, the record SHALL no longer exist in the database after the operation.

**Validates: Requirements 5.1**

### Property 10: Non-Consultant Access Denied

*For any* authenticated user who is not a consultant (user_type != 'consultant' or has no consultant record), all holiday operations SHALL return 403 forbidden.

**Validates: Requirements 6.1**

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
| 422 | Validation error (duplicate date, past date, etc.) |

### Error Response Format

```json
{
    "success": false,
    "message": "رسالة الخطأ",
    "status_code": 422,
    "errors": {
        "holiday_date": ["لا يمكن تكرار نفس تاريخ الإجازة."]
    }
}
```

### Validation Error Messages (Arabic)

- `لا يمكن تكرار نفس تاريخ الإجازة.` - Duplicate date error
- `صيغة التاريخ يجب أن تكون YYYY-MM-DD.` - Invalid date format
- `تاريخ الإجازة يجب أن يكون اليوم أو تاريخًا مستقبليًا.` - Past date error
- `الإجازة المطلوبة غير موجودة.` - Holiday not found
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
- Each test tagged with: **Feature: consultant-holidays-api, Property {number}: {property_text}**

### Test Categories

1. **Authorization Tests** (Properties 1, 10)
   - Test ownership verification
   - Test non-consultant access denial

2. **CRUD Operation Tests** (Properties 2, 3, 4, 8, 9)
   - Test list filtering and ordering
   - Test creation with correct consultant assignment
   - Test update and delete operations

3. **Validation Tests** (Properties 5, 6, 7)
   - Test date format validation
   - Test future date validation
   - Test duplicate date prevention

### Postman Collection

A Postman collection will be created at `postman/consultant-holidays.collection.json` containing:
- All CRUD endpoints
- Example request bodies
- Environment variables for authentication token
