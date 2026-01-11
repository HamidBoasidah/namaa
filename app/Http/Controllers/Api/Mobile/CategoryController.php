<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\DTOs\CategoryMobileDTO;
use App\Http\Traits\SuccessResponse;
use App\Http\Traits\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    use SuccessResponse, ExceptionHandler;

    /**
     * جلب جميع الفئات النشطة
     * GET /api/mobile/categories
     */
    public function index(CategoryService $categoryService)
    {
        $categories = $categoryService->getActiveForMobile();

        $data = $categories->map(function ($category) {
            return CategoryMobileDTO::fromModel($category)->toArray();
        });

        return $this->collectionResponse($data, 'تم جلب قائمة الفئات بنجاح');
    }

    /**
     * جلب الفئات حسب نوع الاستشارة مع عدد المستشارين
     * GET /api/mobile/categories/by-consultation-type/{consultationTypeId}
     */
    public function byConsultationType(int $consultationTypeId, CategoryService $categoryService)
    {
        try {
            $categories = $categoryService->getByConsultationTypeWithConsultantsCount($consultationTypeId);

            $data = $categories->map(function ($category) {
                return CategoryMobileDTO::fromModel($category)->toArrayWithCount();
            });

            return $this->collectionResponse($data, 'تم جلب الفئات مع عدد المستشارين بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('نوع الاستشارة غير موجود');
        }
    }
}
