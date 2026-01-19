<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsultantServicesService;
use App\DTOs\ConsultantServicePublicDTO;
use App\Http\Traits\SuccessResponse;
use App\Http\Traits\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConsultantServiceController extends Controller
{
    use SuccessResponse, ExceptionHandler;

    /**
     * قائمة الخدمات النشطة
     * GET /api/mobile/consultant-services
     * 
     * @param Request $request
     * @param ConsultantServicesService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, ConsultantServicesService $service)
    {
        $perPage = (int) $request->get('per_page', 10);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $services */
        $services = $service->getActiveServices($perPage);

        $services->getCollection()->transform(function ($consultantService) {
            return ConsultantServicePublicDTO::fromModel($consultantService)->toListArray();
        });

        return $this->collectionResponse($services, 'تم جلب قائمة الخدمات بنجاح');
    }

    /**
     * تفاصيل الخدمة
     * GET /api/mobile/consultant-services/{id}
     * 
     * @param int $id
     * @param ConsultantServicesService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, ConsultantServicesService $service)
    {
        try {
            $consultantService = $service->getServiceDetails($id);

            return $this->resourceResponse(
                ConsultantServicePublicDTO::fromModel($consultantService)->toDetailArray(),
                'تم جلب تفاصيل الخدمة بنجاح'
            );
        } catch (NotFoundHttpException) {
            $this->throwNotFoundException('الخدمة غير موجودة أو غير نشطة');
        }
    }
}
