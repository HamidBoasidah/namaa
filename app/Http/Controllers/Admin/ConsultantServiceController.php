<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Services\ConsultantServicesService;
use App\DTOs\ConsultantServiceDTO;

use App\Models\ConsultantService;
use App\Models\Consultant;
use App\Models\Category;
use App\Models\Tag;

use App\Http\Requests\StoreConsultantServiceRequest;
use App\Http\Requests\UpdateConsultantServiceRequest;

class ConsultantServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:consultant-services.view')->only(['index', 'show']);
        $this->middleware('permission:consultant-services.create')->only(['create', 'store']);
        $this->middleware('permission:consultant-services.update')->only(['edit', 'update', 'activate', 'deactivate']);
        $this->middleware('permission:consultant-services.delete')->only(['destroy']);
    }

    public function index(Request $request, ConsultantServicesService $service)
    {
        $perPage = (int) $request->input('per_page', 10);

        $services = $service->paginate($perPage);

        $services->getCollection()->transform(function ($item) {
            return ConsultantServiceDTO::fromModel($item)->toIndexArray();
        });

        return Inertia::render('Admin/ConsultantService/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        $consultants = Consultant::with('user:id,first_name,last_name')
            ->select('id', 'user_id')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->user ? trim("{$c->user->first_name} {$c->user->last_name}") : "#{$c->id}",
            ])
            ->sortBy('name')
            ->values();

        $categories = Category::select('id', 'name')->where('is_active', true)->orderBy('name')->get();
        $tags = Tag::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/ConsultantService/Create', [
            'consultants' => $consultants,
            'categories' => $categories,
            'tags' => $tags,
            'consultationMethods' => [
                ['value' => 'video', 'label' => __('consultant_services.methods.video')],
                ['value' => 'audio', 'label' => __('consultant_services.methods.audio')],
                ['value' => 'text', 'label' => __('consultant_services.methods.text')],
            ],
        ]);
    }

    public function store(StoreConsultantServiceRequest $request, ConsultantServicesService $service)
    {
        $data = $request->validated();

        $service->create($data);

        return redirect()->route('admin.consultant-services.index');
    }

    public function show(ConsultantService $consultant_service, ConsultantServicesService $service)
    {
        $consultant_service->load([
            'consultant.user:id,first_name,last_name,email,phone_number',
            'category:id,name',
            'tags:id,name',
            'includes',
            'targetAudience',
            'deliverables',
        ]);
        
        $dto = ConsultantServiceDTO::fromModel($consultant_service)->toArray();

        return Inertia::render('Admin/ConsultantService/Show', [
            'service' => $dto,
        ]);
    }

    public function edit(ConsultantService $consultant_service)
    {
        $consultant_service->load([
            'consultant.user:id,first_name,last_name,email,phone_number',
            'category:id,name',
            'tags:id,name',
            'includes',
            'targetAudience',
            'deliverables',
        ]);

        $consultants = Consultant::with('user:id,first_name,last_name')
            ->select('id', 'user_id')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->user ? trim("{$c->user->first_name} {$c->user->last_name}") : "#{$c->id}",
            ])
            ->sortBy('name')
            ->values();

        $categories = Category::select('id', 'name')->where('is_active', true)->orderBy('name')->get();
        $tags = Tag::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

        $dto = ConsultantServiceDTO::fromModel($consultant_service)->toArray();

        return Inertia::render('Admin/ConsultantService/Edit', [
            'service' => $dto,
            'consultants' => $consultants,
            'categories' => $categories,
            'tags' => $tags,
            'consultationMethods' => [
                ['value' => 'video', 'label' => __('consultant_services.methods.video')],
                ['value' => 'audio', 'label' => __('consultant_services.methods.audio')],
                ['value' => 'text', 'label' => __('consultant_services.methods.text')],
            ],
        ]);
    }

    public function update(UpdateConsultantServiceRequest $request, ConsultantServicesService $service, ConsultantService $consultant_service)
    {
        $data = $request->validated();

        $service->update($consultant_service->id, $data);

        return redirect()->route('admin.consultant-services.index');
    }

    public function destroy(ConsultantServicesService $service, ConsultantService $consultant_service)
    {
        $service->delete($consultant_service->id);

        return redirect()->route('admin.consultant-services.index');
    }

    public function activate(ConsultantServicesService $service, $id)
    {
        $service->activate($id);
        return back()->with('success', __('messages.activated_successfully'));
    }

    public function deactivate(ConsultantServicesService $service, $id)
    {
        $service->deactivate($id);
        return back()->with('success', __('messages.deactivated_successfully'));
    }
}
