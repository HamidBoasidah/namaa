<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthService
{
    // تسجيل دخول API (token)
    public function loginApi(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات تسجيل الدخول غير صحيحة'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['الحساب معطل، يرجى التواصل مع الإدارة'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'whatsapp_number' => $user->whatsapp_number,
            'type' => $user->type,
            'is_active' => $user->is_active,
            'created_by' => $user->created_by,
            'updated_by' => $user->updated_by,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        return [
            'user' => $userData,
            'token' => $token,
        ];
    }

    // تسجيل دخول session (Inertia/web)
    public function loginWeb(array $credentials)
    {
        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['بيانات تسجيل الدخول غير صحيحة'],
            ]);
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['الحساب معطل، يرجى التواصل مع الإدارة'],
            ]);
        }
        // يمكن هنا إرجاع بيانات المستخدم أو null حسب الحاجة
        return $user;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }


    public function logoutFromAllDevices($user)
    {
        if ($user) {
            $user->tokens()->delete();
        }
        return true;
    }
}
