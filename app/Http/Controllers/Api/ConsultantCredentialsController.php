<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsultantCredentialsService;
use App\DTOs\ConsultantCertificateDTO;
use App\DTOs\ConsultantExperienceDTO;
use App\Http\Requests\StoreConsultantCertificateRequest;
use App\Http\Requests\StoreConsultantExperienceRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Certificate;
use App\Models\ConsultantExperience;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\ValidationException as AppValidationException;

class ConsultantCredentialsController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * عرض جميع الشهادات والخبرات للمستشار الحالي
     */
    public function index(Request $request, ConsultantCredentialsService $service)
    {
        $user = $request->user();

        if ($user->user_type !== 'consultant') {
            $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
        }

        $credentials = $service->getCredentials($user);

        $certificates = $credentials['certificates']->map(function ($cert) {
            return ConsultantCertificateDTO::fromModel($cert)->toArray();
        });

        $experiences = $credentials['experiences']->map(function ($exp) {
            return ConsultantExperienceDTO::fromModel($exp)->toArray();
        });

        return $this->resourceResponse([
            'certificates' => $certificates,
            'experiences' => $experiences,
        ], 'تم جلب الشهادات والخبرات بنجاح');
    }

    /**
     * إضافة شهادة جديدة
     */
    public function storeCertificate(StoreConsultantCertificateRequest $request, ConsultantCredentialsService $service)
    {
        try {
            $user = $request->user();

            if ($user->user_type !== 'consultant') {
                $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
            }

            $this->authorize('create', Certificate::class);

            $data = $request->validated();

            if ($request->hasFile('document_scan_copy')) {
                $data['document_scan_copy'] = $request->file('document_scan_copy');
            }

            $certificate = $service->addCertificate($user, $data);

            return $this->createdResponse(
                ConsultantCertificateDTO::fromModel($certificate)->toArray(),
                'تم إضافة الشهادة بنجاح'
            );
        } catch (AppValidationException $e) {
            return $e->render($request);
        }
    }

    /**
     * حذف شهادة
     */
    public function destroyCertificate(Request $request, ConsultantCredentialsService $service, $id)
    {
        try {
            $user = $request->user();

            if ($user->user_type !== 'consultant') {
                $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
            }

            $consultant = $service->getConsultantByUser($user);

            if (!$consultant) {
                $this->throwNotFoundException('لم يتم العثور على بيانات المستشار');
            }

            $certificate = Certificate::where('id', $id)
                ->where('consultant_id', $consultant->id)
                ->firstOrFail();

            $this->authorize('delete', $certificate);

            $service->deleteCertificate($certificate);

            return $this->deletedResponse('تم حذف الشهادة بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الشهادة غير موجودة');
        }
    }

    /**
     * إضافة خبرة جديدة
     */
    public function storeExperience(StoreConsultantExperienceRequest $request, ConsultantCredentialsService $service)
    {
        try {
            $user = $request->user();

            if ($user->user_type !== 'consultant') {
                $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
            }

            $data = $request->validated();
            $experience = $service->addExperience($user, $data);

            return $this->createdResponse(
                ConsultantExperienceDTO::fromModel($experience)->toArray(),
                'تم إضافة الخبرة بنجاح'
            );
        } catch (AppValidationException $e) {
            return $e->render($request);
        }
    }

    /**
     * حذف خبرة
     */
    public function destroyExperience(Request $request, ConsultantCredentialsService $service, $id)
    {
        try {
            $user = $request->user();

            if ($user->user_type !== 'consultant') {
                $this->throwForbiddenException('هذه الخدمة متاحة للمستشارين فقط');
            }

            $consultant = $service->getConsultantByUser($user);

            if (!$consultant) {
                $this->throwNotFoundException('لم يتم العثور على بيانات المستشار');
            }

            $experience = ConsultantExperience::where('id', $id)
                ->where('consultant_id', $consultant->id)
                ->firstOrFail();

            $this->authorize('delete', $experience);

            $service->deleteExperience($experience);

            return $this->deletedResponse('تم حذف الخبرة بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الخبرة غير موجودة');
        }
    }
}
