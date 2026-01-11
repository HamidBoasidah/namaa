<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsultationTypeService;
use App\DTOs\ConsultationTypeDTO;
use App\Http\Traits\SuccessResponse;
use App\Http\Traits\CanFilter;
use Illuminate\Http\Request;

class ConsultationTypeController extends Controller
{
    use SuccessResponse, CanFilter;

    /**
     * عرض قائمة الوسوم مع فلاتر وترقيم
     */
    public function index(Request $request, ConsultationTypeService $consultationTypeService)
    {
        $perPage = (int) $request->get('per_page', 10);

        $query = $consultationTypeService->query();
        $query = $this->applyFilters(
            $query,
            $request,
            $this->getSearchableFields(),
            $this->getForeignKeyFilters()
        );

        $consultationTypes = $query->latest()->paginate($perPage);

        $consultationTypes->getCollection()->transform(function ($consultationType) {
            return ConsultationTypeDTO::fromModel($consultationType)->toIndexArray();
        });

        return $this->collectionResponse($consultationTypes, 'تم جلب قائمة أنواع الاستشارات بنجاح');
    }

    /**
     * إرجاع أنواع الاستشارات مع عدد المستشارين النشطين لكل نوع
     */
    public function withConsultantsCount(Request $request, ConsultationTypeService $consultationTypeService)
    {
        $perPage = (int) $request->get('per_page', 10);

        $query = $consultationTypeService->query();
        $query = $this->applyFilters(
            $query,
            $request,
            $this->getSearchableFields(),
            $this->getForeignKeyFilters()
        );

        $query = $query->withCount([
            'consultants as consultants_count' => function ($q) {
                $q->where('is_active', true);
            },
        ]);

        $consultationTypes = $query->latest()->paginate($perPage);

        $consultationTypes->getCollection()->transform(function ($consultationType) {
            return ConsultationTypeDTO::fromModel($consultationType)->toIndexArray();
        });

        return $this->collectionResponse($consultationTypes, 'تم جلب أنواع الاستشارات مع عدد المستشارين بنجاح');
    }

    protected function getSearchableFields(): array
    {
        return [
            'name',
            'slug',
            'description',
        ];
    }

    protected function getForeignKeyFilters(): array
    {
        return [
            'is_active' => 'is_active',
        ];
    }
}
