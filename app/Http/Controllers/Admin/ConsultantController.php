<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use App\Services\ConsultantService;
use App\Services\ConsultantWorkingHourService;
use App\Services\ConsultantHolidayService;
use App\Services\ConsultantExperienceService;

use App\DTOs\ConsultantDTO;

use App\Models\Consultant;
use App\Models\ConsultationType;
use App\Models\Governorate;
use App\Models\District;
use App\Models\Area;
use App\Models\User;

use App\Http\Requests\StoreConsultantRequest;
use App\Http\Requests\UpdateConsultantRequest;

class ConsultantController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:consultants.view')->only(['index', 'show']);
        $this->middleware('permission:consultants.create')->only(['create', 'store']);
        $this->middleware('permission:consultants.update')->only(['edit', 'update', 'replaceWeeklyWorkingHours', 'replaceExperiences', 'activate', 'deactivate']);
        $this->middleware('permission:consultants.delete')->only(['destroy']);
    }

    public function index(Request $request, ConsultantService $consultantService)
    {
        $perPage = $request->input('per_page', 9);
        $consultants = $consultantService->paginate($perPage);

        $consultants->getCollection()->transform(function ($consultant) {
            return ConsultantDTO::fromModel($consultant)->toIndexArray();
        });

        return Inertia::render('Admin/Consultant/Index', [
            'consultants' => $consultants
        ]);
    }

    public function create()
    {
        $governorates = Governorate::select('id', 'name_ar', 'name_en')->get();
        $districts    = District::select('id', 'name_ar', 'name_en', 'governorate_id')->get();
        $areas        = Area::select('id', 'name_ar', 'name_en', 'district_id')->get();

        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->where('user_type', 'consultant')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                ];
            });

        return Inertia::render('Admin/Consultant/Create', [
            'governorates' => $governorates,
            'districts' => $districts,
            'areas' => $areas,
            'users' => $users,
        ]);
    }

    public function store(StoreConsultantRequest $request, ConsultantService $consultantService)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image');
        }

        $consultantService->create($data);

        return redirect()->route('admin.consultants.index');
    }

    public function show(Consultant $consultant)
    {
        // ✅ الخيار A: تحميل ساعات العمل والإجازات هنا
        $consultant->load(['workingHours', 'holidays', 'experiences']);

        $consultantDTO = ConsultantDTO::fromModel($consultant)->toArray();

        return Inertia::render('Admin/Consultant/Show', [
            'consultant' => $consultantDTO,
        ]);
    }

    public function edit(Consultant $consultant)
    {
        // ✅ الخيار A: تحميل ساعات العمل والإجازات هنا
        $consultant->load(['workingHours', 'holidays', 'experiences', 'user']);

        $consultantDTO = ConsultantDTO::fromModel($consultant)->toArray();

        // أنواع الاستشارات
        $consultationTypes = ConsultationType::select('id', 'name')->get();

        return Inertia::render('Admin/Consultant/Edit', [
            'consultant' => $consultantDTO,
            'consultation_types' => $consultationTypes,
        ]);
    }

    public function update(UpdateConsultantRequest $request, ConsultantService $consultantService, Consultant $consultant)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $consultant, $request) {
            // تحديث بيانات المستخدم
            $userData = array_filter([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $data['gender'] ?? null,
            ], fn($v) => $v !== null);

            // معالجة الصورة
            if ($request->hasFile('avatar')) {
                // حذف الصورة القديمة إن وجدت
                if ($consultant->user->avatar) {
                    Storage::disk('public')->delete($consultant->user->avatar);
                }
                $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            if (!empty($userData)) {
                $consultant->user->update($userData);
            }

            // تحديث بيانات المستشار
            $consultantData = array_filter([
                'consultation_type_id' => $data['consultation_type_id'] ?? null,
                'years_of_experience' => $data['years_of_experience'] ?? null,
                'is_active' => $data['is_active'] ?? null,
            ], fn($v) => $v !== null);

            if (!empty($consultantData)) {
                $consultant->update($consultantData);
            }
        });

        return redirect()->route('admin.consultants.index');
    }

    public function destroy(ConsultantService $consultantService, Consultant $consultant)
    {
        $consultantService->delete($consultant->id);

        return redirect()->route('admin.consultants.index');
    }

    public function activate(ConsultantService $consultantService, $id)
    {
        $consultantService->activate($id);
        return back()->with('success', 'Consultant activated successfully');
    }

    public function deactivate(ConsultantService $consultantService, $id)
    {
        $consultantService->deactivate($id);
        return back()->with('success', 'Consultant deactivated successfully');
    }

    /**
     * ✅ Weekly Schedule Only
     * استبدال جدول الأسبوع بالكامل
     */
    public function replaceWeeklyWorkingHours(
        Request $request,
        Consultant $consultant,
        ConsultantWorkingHourService $workingHourService
    ) {
        $data = $request->validate([
            'week' => ['required', 'array'],
            'week.*' => ['nullable', 'array'],

            // كل عنصر في اليوم عبارة عن فترة
            'week.*.*.start_time' => ['required', 'date_format:H:i'],
            'week.*.*.end_time'   => ['required', 'date_format:H:i', 'after:week.*.*.start_time'],
            'week.*.*.is_active'  => ['nullable', 'boolean'],
        ]);

        $workingHourService->replaceWeeklySchedule($consultant->id, $data['week']);

        return back()->with('success', 'تم تحديث جدول أوقات العمل للأسبوع بنجاح');
    }

    /**
     * ✅ Replace holidays list
     */
    public function replaceHolidays(
        Request $request,
        Consultant $consultant,
        ConsultantHolidayService $holidayService
    ) {
        $data = $request->validate([
            // allow clearing list by sending an empty array
            'holidays' => ['present', 'array'],

            // each holiday must be today or future, distinct dates
            'holidays.*.holiday_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'distinct'],
            'holidays.*.name' => ['nullable', 'string', 'max:150'],
        ]);

        $holidayService->replaceHolidays($consultant->id, $data['holidays']);

        return back()->with('success', 'تم تحديث قائمة الإجازات بنجاح');
    }

    /**
     * ✅ Replace experiences list
     */
    public function replaceExperiences(
        Request $request,
        Consultant $consultant,
        ConsultantExperienceService $experienceService
    ) {
        $data = $request->validate([
            // allow clearing list by sending an empty array
            'experiences' => ['present', 'array'],

            'experiences.*.name' => ['required', 'string', 'max:255', 'distinct'],
            'experiences.*.is_active' => ['nullable', 'boolean'],
        ]);

        $experienceService->replaceForConsultant($consultant->id, $data['experiences']);

        return back()->with('success', 'تم تحديث قائمة الخبرات بنجاح');
    }
}