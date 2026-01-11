<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreConsultationTypeRequest;
use App\Http\Requests\UpdateConsultationTypeRequest;
use App\Services\ConsultationTypeService;
use App\Models\ConsultationType;
use App\DTOs\ConsultationTypeDTO;
use Inertia\Inertia;

class ConsultationTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:consultation-types.view')->only(['index', 'show']);
        $this->middleware('permission:consultation-types.create')->only(['create', 'store']);
        $this->middleware('permission:consultation-types.update')->only(['edit', 'update', 'activate', 'deactivate']);
        $this->middleware('permission:consultation-types.delete')->only(['destroy']);
    }

    public function index(Request $request, ConsultationTypeService $service)
    {
        $perPage = $request->input('per_page', 10);
        $items = $service->paginate($perPage);
        $items->getCollection()->transform(function ($consultationType) {
            return ConsultationTypeDTO::fromModel($consultationType)->toIndexArray();
        });
        return Inertia::render('Admin/ConsultationType/Index', [
            'consultation_types' => $items,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ConsultationType/Create');
    }

    public function store(StoreConsultationTypeRequest $request, ConsultationTypeService $service)
    {
        $data = $request->validated();
        $icon = $request->file('icon');
        unset($data['icon']);

        $consultationType = $service->create($data);

        if ($icon) {
            $service->uploadIcon($consultationType->id, $icon);
        }
        return redirect()->route('admin.consultation-types.index');
    }

    public function show(ConsultationType $consultation_type)
    {
        $dto = ConsultationTypeDTO::fromModel($consultation_type)->toArray();
        return Inertia::render('Admin/ConsultationType/Show', [
            'consultation_type' => $dto,
        ]);
    }

    public function edit(ConsultationType $consultation_type)
    {
        $dto = ConsultationTypeDTO::fromModel($consultation_type)->toArray();
        return Inertia::render('Admin/ConsultationType/Edit', [
            'consultation_type' => $dto,
        ]);
    }

    public function update(UpdateConsultationTypeRequest $request, ConsultationTypeService $service, ConsultationType $consultation_type)
    {
        $data = $request->validated();
        $icon = $request->file('icon');
        $removeIcon = $request->boolean('remove_icon');

        unset($data['icon'], $data['remove_icon']);

        $service->update($consultation_type->id, $data);

        if ($removeIcon) {
            $service->removeIcon($consultation_type->id);
        } elseif ($icon) {
            $service->uploadIcon($consultation_type->id, $icon);
        }
        return redirect()->route('admin.consultation-types.index');
    }

    public function destroy(ConsultationTypeService $service, ConsultationType $consultation_type)
    {
        $service->delete($consultation_type->id);
        return redirect()->route('admin.consultation-types.index');
    }

    public function activate(ConsultationTypeService $service, $id)
    {
        $service->activate($id);
        return back()->with('success', 'Consultation type activated successfully');
    }

    public function deactivate(ConsultationTypeService $service, $id)
    {
        $service->deactivate($id);
        return back()->with('success', 'Consultation type deactivated successfully');
    }
}
