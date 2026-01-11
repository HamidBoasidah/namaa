<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\ConsultantService;
use App\DTOs\ConsultantMobileDTO;
use App\Http\Traits\SuccessResponse;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\CanFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConsultantController extends Controller
{
    use SuccessResponse, ExceptionHandler, CanFilter;

    /**
     * جلب المستشارين حسب الفئة
     * GET /api/mobile/consultants/by-category/{categoryId}
     */
    public function byCategory(int $categoryId, Request $request, ConsultantService $consultantService)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            
            $consultants = $consultantService->getByCategory($categoryId, $perPage);

            $consultants->getCollection()->transform(function ($consultant) use ($consultantService) {
                $serviceCategories = $consultantService->getServiceCategories($consultant);
                return ConsultantMobileDTO::fromModel($consultant, $serviceCategories)->toArray();
            });

            return $this->collectionResponse($consultants, 'تم جلب المستشارين بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الفئة غير موجودة');
        }
    }

    /**
     * عرض المستشارين مع خيارات الترتيب
     * GET /api/mobile/consultants
     * Query params: sort_by (experience|rating|reviews), per_page
     */
    public function index(Request $request, ConsultantService $consultantService)
    {
        $perPage = (int) $request->get('per_page', 10);
        $sortBy = $request->get('sort_by');

        // التحقق من صحة قيمة الترتيب
        $validSortOptions = ['experience', 'rating', 'reviews', null];
        if ($sortBy !== null && !in_array($sortBy, $validSortOptions)) {
            return response()->json([
                'success' => false,
                'message' => 'قيمة الترتيب غير صالحة. القيم المسموحة: experience, rating, reviews',
                'status_code' => 422
            ], 422);
        }

        $consultants = $consultantService->getForMobile($sortBy, $perPage);

        $consultants->getCollection()->transform(function ($consultant) use ($consultantService) {
            $serviceCategories = $consultantService->getServiceCategories($consultant);
            return ConsultantMobileDTO::fromModel($consultant, $serviceCategories)->toArray();
        });

        return $this->collectionResponse($consultants, 'تم جلب قائمة المستشارين بنجاح');
    }
}
