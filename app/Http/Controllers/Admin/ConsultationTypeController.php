<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreConsultationTypeRequest;
use App\Http\Requests\UpdateConsultationTypeRequest;
use App\Services\ConsultationTypeService;
use App\Models\ConsultationType;
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
        $service->create($request->validated());
        return redirect()->route('admin.consultation-types.index');
    }

    public function show(ConsultationType $consultation_type)
    {
        return Inertia::render('Admin/ConsultationType/Show', [
            'consultation_type' => $consultation_type,
        ]);
    }

    public function edit(ConsultationType $consultation_type)
    {
        return Inertia::render('Admin/ConsultationType/Edit', [
            'consultation_type' => $consultation_type,
        ]);
    }

    public function update(UpdateConsultationTypeRequest $request, ConsultationTypeService $service, ConsultationType $consultation_type)
    {
        $service->update($consultation_type->id, $request->validated());
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
