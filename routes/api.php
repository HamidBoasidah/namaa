<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Authentication Routes
Route::post('/register', [App\Http\Controllers\Api\RegisteredUserController::class, 'store']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/logout-all-devices', [App\Http\Controllers\Api\AuthController::class, 'logoutFromAllDevices'])->middleware('auth:sanctum');
Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/update-password', [App\Http\Controllers\Api\AuthController::class, 'updatePassword'])->middleware('auth:sanctum');
//Route::post('/register', [App\Http\Controllers\Api\RegisteredUserController::class, 'store']);

// Consultation Types (API)
Route::get('/consultation-types', [App\Http\Controllers\Api\ConsultationTypeController::class, 'index']);
Route::get('/consultation-types/with-count', [App\Http\Controllers\Api\ConsultationTypeController::class, 'withConsultantsCount']);

// Consultant Profile (API)
Route::prefix('consultant/profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ConsultantProfileController::class, 'show']);
    Route::put('/', [App\Http\Controllers\Api\ConsultantProfileController::class, 'update']);
    Route::post('/', [App\Http\Controllers\Api\ConsultantProfileController::class, 'update']); // For multipart/form-data
    Route::delete('/', [App\Http\Controllers\Api\ConsultantProfileController::class, 'destroy']);
});

// Consultant Credentials (Certificates & Experiences)
Route::prefix('consultant/credentials')->middleware('auth:sanctum')->group(function () {
    // Get all certificates and experiences
    Route::get('/', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'index']);

    // Certificates
    Route::post('/certificates', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'storeCertificate']);
    Route::delete('/certificates/{id}', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'destroyCertificate']);

    // Experiences
    Route::post('/experiences', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'storeExperience']);
    Route::put('/experiences/{id}', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'updateExperience']);
    Route::delete('/experiences/{id}', [App\Http\Controllers\Api\ConsultantCredentialsController::class, 'destroyExperience']);
});

// Consultant Working Hours (API)
Route::prefix('consultant/working-hours')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'destroy']);
    Route::post('/{id}/activate', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'activate']);
    Route::post('/{id}/deactivate', [App\Http\Controllers\Api\ConsultantWorkingHourController::class, 'deactivate']);
});

// Consultant Holidays (API)
Route::prefix('consultant/holidays')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ConsultantHolidayController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\ConsultantHolidayController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\ConsultantHolidayController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\ConsultantHolidayController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\ConsultantHolidayController::class, 'destroy']);
});

// Mobile App APIs
Route::prefix('mobile')->group(function () {
    // Categories
    Route::get('/categories', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/categories/by-consultation-type/{consultationTypeId}', [App\Http\Controllers\Api\CategoryController::class, 'byConsultationType']);
    
    // Consultants
    Route::get('/consultants', [App\Http\Controllers\Api\ConsultantController::class, 'index']);
    Route::get('/consultants/by-category/{categoryId}', [App\Http\Controllers\Api\ConsultantController::class, 'byCategory']);
    Route::get('/consultants/{consultantId}/profile', [App\Http\Controllers\Api\ConsultantController::class, 'profile']);
    
    // Consultant Services
    Route::get('/consultant-services', [App\Http\Controllers\Api\ConsultantServiceController::class, 'index']);
    Route::get('/consultant-services/{id}', [App\Http\Controllers\Api\ConsultantServiceController::class, 'show']);
});

// Bookings API
Route::prefix('bookings')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\BookingController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\BookingController::class, 'show']);
    Route::post('/pending', [App\Http\Controllers\Api\BookingController::class, 'storePending']);
    Route::post('/{id}/confirm', [App\Http\Controllers\Api\BookingController::class, 'confirm']);
    Route::post('/{id}/cancel', [App\Http\Controllers\Api\BookingController::class, 'cancel']);
});

// Consultant Available Slots (public endpoint for viewing availability)
Route::get('/consultants/{id}/available-slots', [App\Http\Controllers\Api\ConsultantAvailabilityController::class, 'availableSlots']);

// All Reviews (public - ordered by rating)
Route::get('/reviews', [App\Http\Controllers\Api\ReviewController::class, 'index']);

// Reviews API
Route::prefix('reviews')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [App\Http\Controllers\Api\ReviewController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\Api\ReviewController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\ReviewController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\ReviewController::class, 'destroy']);
});

// My Reviews (authenticated client)
Route::get('/my/reviews', [App\Http\Controllers\Api\ReviewController::class, 'myReviews'])->middleware('auth:sanctum');

// Consultant Reviews (public/optional auth)
Route::get('/consultants/{id}/reviews', [App\Http\Controllers\Api\ConsultantReviewsController::class, 'index']);

// Consultant Service Reviews (public/optional auth)
Route::get('/consultant-services/{id}/reviews', [App\Http\Controllers\Api\ConsultantReviewsController::class, 'serviceReviews']);