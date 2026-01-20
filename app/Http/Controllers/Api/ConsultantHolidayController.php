<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ConsultantHoliday;
use App\Services\ConsultantHolidayService;
use App\Http\Requests\StoreConsultantHolidayRequest;
use App\Http\Requests\UpdateConsultantHolidayRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConsultantHolidayController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Transform holiday to array
     */
    protected function transformHoliday(ConsultantHoliday $holiday): array
    {
        return [
            'id' => $holiday->id,
            'consultant_id' => $holiday->consultant_id,
            'holiday_date' => $holiday->holiday_date?->format('Y-m-d'),
            'name' => $holiday->name,
            'created_at' => $holiday->created_at?->toISOString(),
            'updated_at' => $holiday->updated_at?->toISOString(),
        ];
    }

    /**
     * عرض قائمة الإجازات للمستشار الحالي
     */
    public function index(Request $request, ConsultantHolidayService $service): JsonResponse
    {
        $this->authorize('viewAny', ConsultantHoliday::class);

        $consultant = Consultant::where('user_id', $request->user()->id)->first();
        $perPage = (int) $request->get('per_page', 10);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $holidays */
        $holidays = $service->getQueryForConsultant($consultant->id)
            ->orderBy('holiday_date')
            ->paginate($perPage);

        $holidays->getCollection()->transform(function ($holiday) {
            return $this->transformHoliday($holiday);
        });

        return $this->collectionResponse($holidays, 'تم جلب قائمة الإجازات بنجاح');
    }

    /**
     * عرض إجازة محددة
     */
    public function show(ConsultantHolidayService $service, $id): JsonResponse
    {
        try {
            $holiday = $service->find($id);
            $this->authorize('view', $holiday);

            return $this->resourceResponse(
                $this->transformHoliday($holiday),
                'تم جلب بيانات الإجازة بنجاح'
            );
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الإجازة المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }

    /**
     * إنشاء إجازة جديدة
     */
    public function store(StoreConsultantHolidayRequest $request, ConsultantHolidayService $service): JsonResponse
    {
        $this->authorize('create', ConsultantHoliday::class);

        $consultant = Consultant::where('user_id', $request->user()->id)->first();
        $data = $request->validated();
        $data['consultant_id'] = $consultant->id;

        $holiday = $service->create($data);

        return $this->createdResponse(
            $this->transformHoliday($holiday),
            'تم إنشاء الإجازة بنجاح'
        );
    }

    /**
     * تحديث إجازة
     */
    public function update(UpdateConsultantHolidayRequest $request, ConsultantHolidayService $service, $id): JsonResponse
    {
        try {
            $holiday = $service->find($id);
            $this->authorize('update', $holiday);

            $consultant = Consultant::where('user_id', $request->user()->id)->first();
            $data = $request->validated();
            $data['consultant_id'] = $consultant->id;

            $updated = $service->update($id, $data);

            return $this->updatedResponse(
                $this->transformHoliday($updated),
                'تم تحديث الإجازة بنجاح'
            );
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الإجازة المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }

    /**
     * حذف إجازة
     */
    public function destroy(ConsultantHolidayService $service, $id): JsonResponse
    {
        try {
            $holiday = $service->find($id);
            $this->authorize('delete', $holiday);

            $service->delete($id);

            return $this->deletedResponse('تم حذف الإجازة بنجاح');
        } catch (ModelNotFoundException) {
            $this->throwNotFoundException('الإجازة المطلوبة غير موجودة');
            throw new ModelNotFoundException();
        }
    }
}
