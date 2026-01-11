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
//Route::post('/register', [App\Http\Controllers\Api\RegisteredUserController::class, 'store']);

// Consultation Types (API)
Route::get('/consultation-types', [App\Http\Controllers\Api\ConsultationTypeController::class, 'index']);

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