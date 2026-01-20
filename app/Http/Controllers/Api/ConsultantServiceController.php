<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultantService;
use App\Models\Consultant;
use App\Services\ConsultantServicesService;
use App\DTOs\ConsultantServiceDTO;
use App\Http\Requests\Api\StoreConsultantServiceRequest;
use App\Http\Requests\Api\UpdateConsultantServiceRequest;
use App\Http\Traits\SuccessResponse;
use App\Http\Traits\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConsultantServiceController extends Controller
{
    use SuccessResponse, ExceptionHandler;

    /**
     * عرض قائمة الخدمات النشطة (عام - بدون تسجيل دخول)
     * GET /api/consultant-services
     */
    public function index(Request $request, ConsultantServicesService $service): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $services */
        $services = $service->getActiveServices($perPage);

        $services->getCollection()->transform(function ($consultantService) {
            return ConsultantServiceDTO::fromModel($consultantService)->toListArray();
        });

        return $this->collectionResponse($services, 'تم جلب قائمة الخدمات بنجاح');
    }

    /**
     * عرض قائمة خدمات المستشار الحالي
     * GET /api/consultant/services
     */
    public function myServices(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ConsultantService::class);

        $consultant = Consultant::where('user_id', $request->user()->id)->first();
        
        if (!$consultant) {
            $this->throwNotFoundException('لم يتم العثور على ملف المستشار');
        }

        $perPage = (int) $request->get('per_page', 10);

        $services = ConsultantService::with(['category', 'tags', 'includes', 'targetAudience', 'deliverables'])
            ->where('consultant_id', $consultant->id)
            ->latest()
            ->paginate($perPage);

        $services->getCollection()->transform(function ($consultantService) {
            return ConsultantServiceDTO::fromModel($consultantService)->toArray();
        });

        return $this->collectionResponse($services, 'تم جلب قائمة الخدمات بنجاح');
    }

    /**
     * إنشاء خدمة جديدة
     * POST /api/consultant/services
     */
    public function store(StoreConsultantServiceRequest $request, ConsultantServicesService $service): JsonResponse
    {
        $this->authorize('create', ConsultantService::class);

        $validated = $request->validated();
        $validated['consultant_id'] = $request->getConsultant()->id;

        $consultantService = $service->create($validated);
        $consultantService->load(['category', 'tags', 'includes', 'targetAudience', 'deliverables']);

        return $this->createdResponse(
            ConsultantServiceDTO::fromModel($consultantService)->toArray(),
            'تم إنشاء الخدمة بنجاح'
        );
    }

    /**
     * عرض تفاصيل خدمة (عام - بدون تسجيل دخول)
     * GET /api/consultant-services/{id}
     */
    public function show(int $id, ConsultantServicesService $service): JsonResponse
    {
        try {
            $consultantService = $service->getServiceDetails($id);

            return $this->resourceResponse(
                ConsultantServiceDTO::fromModel($consultantService)->toDetailArray(),
                'تم جلب تفاصيل الخدمة بنجاح'
            );
        } catch (NotFoundHttpException) {
            $this->throwNotFoundException('الخدمة غير موجودة أو غير نشطة');
            throw new NotFoundHttpException();
        }
    }

    /**
     * تحديث خدمة
     * PUT /api/consultant/services/{id}
     */
    public function update(UpdateConsultantServiceRequest $request, ConsultantServicesService $service, $id): JsonResponse
    {
        $consultantService = $service->find($id);
        $this->authorize('update', $consultantService);

        $validated = $request->validated();
        $validated['consultant_id'] = $request->getConsultant()->id;

        $updated = $service->update($id, $validated);
        $updated->load(['category', 'tags', 'includes', 'targetAudience', 'deliverables']);

        return $this->updatedResponse(
            ConsultantServiceDTO::fromModel($updated)->toArray(),
            'تم تحديث الخدمة بنجاح'
        );
    }

    /**
     * حذف خدمة
     * DELETE /api/consultant/services/{id}
     */
    public function destroy(ConsultantServicesService $service, $id): JsonResponse
    {
        try {
            $consultantService = $service->find($id);
            $this->authorize('delete', $consultantService);

            $service->delete($id);

            return $this->deletedResponse('تم حذف الخدمة بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الخدمة المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }
}
