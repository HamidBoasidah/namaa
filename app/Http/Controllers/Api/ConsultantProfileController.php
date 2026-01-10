<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsultantProfileService;
use App\DTOs\ConsultantProfileDTO;
use App\Http\Requests\UpdateConsultantProfileRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use Illuminate\Http\Request;
use App\Exceptions\ValidationException as AppValidationException;

class ConsultantProfileController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * عرض بيانات الملف الشخصي للمستشار الحالي
     */
    public function show(Request $request, ConsultantProfileService $profileService)
    {
        $user = $request->user();

        // التحقق من أن المستخدم مستشار
        if ($user->user_type !== 'consultant') {
            $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
        }

        $profile = $profileService->getProfile($user);

        return $this->resourceResponse(
            ConsultantProfileDTO::fromUser($profile['user'], $profile['consultant'])->toArray(),
            'تم جلب بيانات الملف الشخصي بنجاح'
        );
    }

    /**
     * تحديث بيانات الملف الشخصي للمستشار الحالي
     */
    public function update(UpdateConsultantProfileRequest $request, ConsultantProfileService $profileService)
    {
        try {
            $user = $request->user();

            // التحقق من أن المستخدم مستشار
            if ($user->user_type !== 'consultant') {
                $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
            }

            $data = $request->validated();
            $profile = $profileService->updateProfile($user, $data);

            return $this->updatedResponse(
                ConsultantProfileDTO::fromUser($profile['user'], $profile['consultant'])->toArray(),
                'تم تحديث الملف الشخصي بنجاح'
            );
        } catch (AppValidationException $e) {
            return $e->render($request);
        }
    }

    /**
     * حذف حساب المستشار الحالي
     */
    public function destroy(Request $request, ConsultantProfileService $profileService)
    {
        $user = $request->user();

        // التحقق من أن المستخدم مستشار
        if ($user->user_type !== 'consultant') {
            $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
        }

        $profileService->deleteAccount($user);

        return $this->deletedResponse('تم حذف الحساب بنجاح');
    }
}
