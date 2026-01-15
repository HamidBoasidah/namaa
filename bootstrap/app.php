<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/admin.php'
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
            $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'guest' =>  \App\Http\Middleware\RedirectIfAuthenticated::class,
            'auth' =>  	\App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle Custom Application Exceptions
        $exceptions->render(function (\App\Exceptions\ApplicationException $e, $request) {
            if ($request->expectsJson()) {
                return $e->render($request);
            }
        });

        // Handle Validation Exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'error_code' => 'VALIDATION_ERROR',
                    'status_code' => 422,
                    'errors' => $e->errors(),
                    'timestamp' => now()->toISOString(),
                ], 422);
            }
        });

        // Handle Authentication Exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول للوصول لهذا المورد',
                    'error_code' => 'UNAUTHENTICATED',
                    'status_code' => 401,
                    'timestamp' => now()->toISOString(),
                ], 401);
            }
        });

        // Handle Model Not Found Exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'المورد المطلوب غير موجود',
                    'error_code' => 'NOT_FOUND',
                    'status_code' => 404,
                    'timestamp' => now()->toISOString(),
                ], 404);
            }
        });

        // Handle Access Denied Exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول لهذا المورد',
                    'error_code' => 'ACCESS_DENIED',
                    'status_code' => 403,
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        });

        // Handle Authorization Exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتنفيذ هذا الإجراء',
                    'error_code' => 'UNAUTHORIZED',
                    'status_code' => 403,
                    'timestamp' => now()->toISOString(),
                ], 403);
            }
        });

        // Handle Not Found HTTP Exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الصفحة المطلوبة غير موجودة',
                    'error_code' => 'ROUTE_NOT_FOUND',
                    'status_code' => 404,
                    'timestamp' => now()->toISOString(),
                ], 404);
            }
        });

        // Handle Method Not Allowed Exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'طريقة الطلب غير مسموح بها',
                    'error_code' => 'METHOD_NOT_ALLOWED',
                    'status_code' => 405,
                    'timestamp' => now()->toISOString(),
                ], 405);
            }
        });

        // Handle Query Exceptions (Database errors)
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson()) {
                // Handle duplicate entry
                if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'البيانات المراد إدخالها موجودة بالفعل',
                        'error_code' => 'DUPLICATE_ENTRY',
                        'status_code' => 422,
                        'timestamp' => now()->toISOString(),
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في قاعدة البيانات',
                    'error_code' => 'DATABASE_ERROR',
                    'status_code' => 500,
                    'timestamp' => now()->toISOString(),
                ], 500);
            }
        });

        // Handle Too Many Requests (Rate Limiting)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم تجاوز الحد المسموح من الطلبات. حاول مرة أخرى لاحقاً',
                    'error_code' => 'TOO_MANY_REQUESTS',
                    'status_code' => 429,
                    'timestamp' => now()->toISOString(),
                ], 429);
            }
        });
    })->create();
