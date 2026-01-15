<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ConsultantWorkingHour;
use App\Services\ConsultantWorkingHourService;
use App\Http\Requests\StoreConsultantWorkingHourRequest;
use App\Http\Requests\UpdateConsultantWorkingHourRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConsultantWorkingHourController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get consultant for authenticated user
     */
    protected function getConsultant(Request $request): ?Consultant
    {
        return Consultant::where('user_id', $request->user()->id)->first();
    }

    /**
     * Get day name in Arabic
     */
    protected function getDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'الأحد',
            1 => 'الإثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];
        return $days[$dayOfWeek] ?? '';
    }


    /**
     * Transform working hour to array
     */
    protected function transformWorkingHour(ConsultantWorkingHour $workingHour): array
    {
        return [
            'id' => $workingHour->id,
            'consultant_id' => $workingHour->consultant_id,
            'day_of_week' => $workingHour->day_of_week,
            'day_name' => $this->getDayName($workingHour->day_of_week),
            'start_time' => $workingHour->start_time,
            'end_time' => $workingHour->end_time,
            'is_active' => $workingHour->is_active,
            'created_at' => $workingHour->created_at?->toISOString(),
            'updated_at' => $workingHour->updated_at?->toISOString(),
        ];
    }

    /**
     * عرض قائمة ساعات العمل للمستشار الحالي
     */
    public function index(Request $request, ConsultantWorkingHourService $service): JsonResponse
    {
        $consultant = $this->getConsultant($request);
        
        if (!$consultant) {
            $this->throwForbiddenException('غير مصرح لك بالوصول لهذا المورد');
        }

        $perPage = (int) $request->get('per_page', 10);

        $query = $service->getQueryForConsultant($consultant->id);
        
        $workingHours = $query
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($perPage);

        $workingHours->getCollection()->transform(function ($workingHour) {
            return $this->transformWorkingHour($workingHour);
        });

        return $this->collectionResponse($workingHours, 'تم جلب قائمة ساعات العمل بنجاح');
    }

    /**
     * عرض ساعة عمل محددة
     */
    public function show(Request $request, ConsultantWorkingHourService $service, $id): JsonResponse
    {
        $consultant = $this->getConsultant($request);
        
        if (!$consultant) {
            $this->throwForbiddenException('غير مصرح لك بالوصول لهذا المورد');
        }

        try {
            $workingHour = $service->find($id);

            $this->authorize('view', $workingHour);

            return $this->resourceResponse(
                $this->transformWorkingHour($workingHour),
                'تم جلب بيانات ساعة العمل بنجاح'
            );
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('ساعة العمل المطلوبة غير موجودة');
            throw new ModelNotFoundException(); // This line won't execute but satisfies static analysis
        }
    }


    /**
     * إنشاء ساعة عمل جديدة
     */
    public function store(StoreConsultantWorkingHourRequest $request, ConsultantWorkingHourService $service): JsonResponse
    {
        $consultant = $this->getConsultant($request);
        
        if (!$consultant) {
            $this->throwForbiddenException('غير مصرح لك بالوصول لهذا المورد');
        }

        $this->authorize('create', ConsultantWorkingHour::class);

        $data = $request->validated();
        $data['consultant_id'] = $consultant->id;

        $workingHour = $service->create($data);

        return $this->createdResponse(
            $this->transformWorkingHour($workingHour),
            'تم إنشاء ساعة العمل بنجاح'
        );
    }

    /**
     * تحديث ساعة عمل
     */
    public function update(UpdateConsultantWorkingHourRequest $request, ConsultantWorkingHourService $service, $id): JsonResponse
    {
        $consultant = $this->getConsultant($request);
        
        if (!$consultant) {
            $this->throwForbiddenException('غير مصرح لك بالوصول لهذا المورد');
        }

        try {
            $workingHour = $service->find($id);

            $this->authorize('update', $workingHour);

            $data = $request->validated();
            $data['consultant_id'] = $consultant->id;

            $updated = $service->update($id, $data);

            return $this->updatedResponse(
                $this->transformWorkingHour($updated),
                'تم تحديث ساعة العمل بنجاح'
            );
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('ساعة العمل المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }


    /**
     * حذف ساعة عمل
     */
    public function destroy(Request $request, ConsultantWorkingHourService $service, $id): JsonResponse
    {
        $consultant = $this->getConsultant($request);
        
        if (!$consultant) {
            $this->throwForbiddenException('غير مصرح لك بالوصول لهذا المورد');
        }

        try {
            $workingHour = $service->find($id);

            $this->authorize('delete', $workingHour);

            $service->delete($id);

            return $this->deletedResponse('تم حذف ساعة العمل بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('ساعة العمل المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }

    
}
