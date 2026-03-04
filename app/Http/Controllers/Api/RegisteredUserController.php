<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(StoreUserRequest $request, UserService $userService): JsonResponse
    {
        // التحقق من وجود الحساب مسبقاً (بريد أو رقم جوال أو واتساب) وإرجاع رسالة موحدة
        $exists = User::where('email', $request->input('email'))
            ->orWhere('phone_number', $request->input('phone_number'))
            ->when($request->filled('whatsapp_number'), fn ($q) => $q->orWhere('whatsapp_number', $request->input('whatsapp_number')))
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'الحساب موجود مسبقا, يرجى تسجيل الدخول',
            ], 422);
        }

        $data = $request->validated();

        // إذا وُجد ملف مرفق نُمرره ضمن البيانات ليتولى الـ Service التعامل معه
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        $user = $userService->create($data);

        // Create token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'طلب التسجيل تم بنجاح',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
