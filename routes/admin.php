<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\ConsultantController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\ProfileController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ConsultationTypeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConsultantServiceController;
use App\Http\Controllers\Admin\BookingController;
use App\Support\RoutePermissions;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        //Route::get('register', [RegisteredUserController::class, 'create'])
            //->name('register');

        //Route::post('register', [RegisteredUserController::class, 'store']);
        // Registration is disabled for admins via UI — redirect to login
        Route::get('register', function () {
            return redirect()->route('admin.login');
        })->name('register');

        Route::post('register', function () {
            return redirect()->route('admin.login');
        });

        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
});

Route::middleware('auth:admin')
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', fn () => Inertia('Dashboard'))
            ->name('dashboard')
            ->middleware(RoutePermissions::can('dashboard.view'));

        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])
            ->name('profile');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('/profile', [ProfileController::class, 'update'])
            ->name('profile.update.post');

        // Governorates
        Route::resource('governorates', GovernorateController::class)
            ->names('governorates');

        Route::patch('governorates/{id}/activate', [GovernorateController::class, 'activate'])
            ->name('governorates.activate');

        Route::patch('governorates/{id}/deactivate', [GovernorateController::class, 'deactivate'])
            ->name('governorates.deactivate');

        // Districts
        Route::resource('districts', DistrictController::class)
            ->names('districts');

        Route::patch('districts/{id}/activate', [DistrictController::class, 'activate'])
            ->name('districts.activate');

        Route::patch('districts/{id}/deactivate', [DistrictController::class, 'deactivate'])
            ->name('districts.deactivate');

        // Areas
        Route::resource('areas', AreaController::class)
            ->names('areas');

        Route::patch('areas/{id}/activate', [AreaController::class, 'activate'])
            ->name('areas.activate');

        Route::patch('areas/{id}/deactivate', [AreaController::class, 'deactivate'])
            ->name('areas.deactivate');

        // Certificates
        Route::get('certificates/{certificate}/document/view', [CertificateController::class, 'viewDocument'])
            ->name('certificates.document.view');
        Route::get('certificates/{certificate}/document/download', [CertificateController::class, 'downloadDocument'])
            ->name('certificates.document.download');
        Route::resource('certificates', CertificateController::class)
            ->names('certificates');

        // Tags
        Route::resource('tags', TagController::class)
            ->names('tags');

        Route::patch('tags/{id}/activate', [TagController::class, 'activate'])
            ->name('tags.activate');

        Route::patch('tags/{id}/deactivate', [TagController::class, 'deactivate'])
            ->name('tags.deactivate');

        // Consultation Types
        Route::resource('consultation-types', ConsultationTypeController::class)
            ->names('consultation-types');

        Route::patch('consultation-types/{id}/activate', [ConsultationTypeController::class, 'activate'])
            ->name('consultation-types.activate');

        Route::patch('consultation-types/{id}/deactivate', [ConsultationTypeController::class, 'deactivate'])
            ->name('consultation-types.deactivate');

        // Categories
        Route::resource('categories', CategoryController::class)
            ->names('categories');

        Route::patch('categories/{id}/activate', [CategoryController::class, 'activate'])
            ->name('categories.activate');

        Route::patch('categories/{id}/deactivate', [CategoryController::class, 'deactivate'])
            ->name('categories.deactivate');

        // Category Icons
        Route::post('categories/{id}/icon', [CategoryController::class, 'uploadIcon'])
            ->name('categories.uploadIcon');

        Route::delete('categories/{id}/icon', [CategoryController::class, 'removeIcon'])
            ->name('categories.removeIcon');

        // Users
        Route::resource('users', UserController::class)
            ->names('users');

        Route::patch('users/{id}/activate', [UserController::class, 'activate'])
            ->name('users.activate');

        Route::patch('users/{id}/deactivate', [UserController::class, 'deactivate'])
            ->name('users.deactivate');

        // Consultants
        Route::resource('consultants', ConsultantController::class)
            ->names('consultants');

        Route::patch('consultants/{id}/activate', [ConsultantController::class, 'activate'])
            ->name('consultants.activate');

        Route::patch('consultants/{id}/deactivate', [ConsultantController::class, 'deactivate'])
            ->name('consultants.deactivate');

        
        // ✅ Weekly schedule (replace)
        Route::put('consultants/{consultant}/working-hours/replace', [ConsultantController::class, 'replaceWeeklyWorkingHours'])
            ->name('consultants.working-hours.replace');

        Route::put('consultants/{consultant}/holidays/replace', [ConsultantController::class, 'replaceHolidays'])
            ->name('consultants.holidays.replace');

        Route::put('consultants/{consultant}/experiences/replace', [ConsultantController::class, 'replaceExperiences'])
            ->name('consultants.experiences.replace');


        // Consultant Services
        Route::resource('consultant-services', ConsultantServiceController::class);

        Route::patch('consultant-services/{id}/activate', [ConsultantServiceController::class, 'activate'])
            ->name('consultant-services.activate');

        Route::patch('consultant-services/{id}/deactivate', [ConsultantServiceController::class, 'deactivate'])
            ->name('consultant-services.deactivate');

        // Bookings
        Route::resource('bookings', BookingController::class)
            ->names('bookings');

        Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->name('bookings.cancel');

        Route::get('bookings-available-slots', [BookingController::class, 'availableSlots'])
            ->name('bookings.available-slots');


        // Admins (managers of the system)
        Route::resource('admins', AdminController::class)
            ->names('admins');
        Route::patch('admins/{admin}/activate', [AdminController::class, 'activate'])
            ->name('admins.activate');

        Route::patch('admins/{admin}/deactivate', [AdminController::class, 'deactivate'])
            ->name('admins.deactivate');


        // Roles
        Route::resource('roles', RoleController::class)
            ->names('roles');

        Route::patch('roles/{id}/activate', [RoleController::class, 'activate'])
            ->name('roles.activate')
            ->middleware(RoutePermissions::can('roles.update'));

        Route::patch('roles/{id}/deactivate', [RoleController::class, 'deactivate'])
            ->name('roles.deactivate')
            ->middleware(RoutePermissions::can('roles.update'));

        // Permissions
        Route::get('permissions', [PermissionController::class, 'index'])
            ->name('permissions.index');

        // Activity Log
        Route::resource('activitylogs', ActivityLogController::class)
            ->only(['index', 'show'])
            ->names('activitylogs');


        Route::get('verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('password.confirm');

        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

        Route::put('password', [PasswordController::class, 'update'])->name('password.update');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
});
