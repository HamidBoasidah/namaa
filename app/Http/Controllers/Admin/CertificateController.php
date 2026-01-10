<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CertificateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:certificates.view')->only(['index', 'show', 'viewDocument', 'downloadDocument']);
        $this->middleware('permission:certificates.create')->only(['create', 'store']);
        $this->middleware('permission:certificates.update')->only(['edit', 'update']);
        $this->middleware('permission:certificates.delete')->only(['destroy']);
    }

    public function index(Request $request, CertificateService $certificateService): Response
    {
        $perPage = (int) $request->input('per_page', 10);

        $certificates = $certificateService->paginate($perPage);

        $certificates->getCollection()->transform(function ($certificate) {
            $dto = CertificateDTO::fromModel($certificate)->toArray();

            return $dto;
        });

        return Inertia::render('Admin/Certificate/Index', [
            'certificates' => $certificates,
        ]);
    }

    public function create(): Response
    {
        // need consultants for selection
        $consultants = \App\Models\Consultant::with('user:id,first_name,last_name,email,phone_number')
            ->get()
            ->map(function ($consultant) {
                return [
                    'id' => $consultant->id,
                    'first_name' => $consultant->user->first_name ?? '',
                    'last_name' => $consultant->user->last_name ?? '',
                    'email' => $consultant->user->email ?? '',
                    'phone_number' => $consultant->user->phone_number ?? '',
                ];
            });

        return Inertia::render('Admin/Certificate/Create', [
            'consultants' => $consultants,
        ]);
    }

    public function store(StoreCertificateRequest $request, CertificateService $certificateService): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('document_scan_copy')) {
            $data['document_scan_copy'] = $request->file('document_scan_copy');
        }

        try {
            $certificateService->create($data);
        } catch (\App\Exceptions\ValidationException $e) {
            // For admin (Inertia/web) convert validation exception to redirect with errors
            // so the UI can display the message similar to form validation.
            $errors = [];
            if (method_exists($e, 'errors')) {
                $errors = $e->errors();
            } else {
                $errors = ['user_id' => $e->getMessage()];
            }

            return back()->withErrors($errors)->withInput();
        }

        return redirect()->route('admin.certificates.index');
    }

    public function show(Certificate $certificate): Response
    {
        $dto = CertificateDTO::fromModel($certificate)->toArray();
        /*$dto['user'] = $certificate->user
            ? $certificate->user->only(['id', 'first_name', 'last_name', 'email', 'phone_number', 'avatar'])
            : null;*/

        return Inertia::render('Admin/Certificate/Show', [
            'certificate' => $dto,
        ]);
    }

    public function edit(Certificate $certificate): Response
    {
        $dto = CertificateDTO::fromModel($certificate)->toArray();

        // need consultants for selection
        $consultants = \App\Models\Consultant::with('user:id,first_name,last_name,email,phone_number')
            ->get()
            ->map(function ($consultant) {
                return [
                    'id' => $consultant->id,
                    'first_name' => $consultant->user->first_name ?? '',
                    'last_name' => $consultant->user->last_name ?? '',
                    'email' => $consultant->user->email ?? '',
                    'phone_number' => $consultant->user->phone_number ?? '',
                ];
            });

        return Inertia::render('Admin/Certificate/Edit', [
            'certificate' => $dto,
            'consultants' => $consultants,
        ]);
    }

    public function update(UpdateCertificateRequest $request, CertificateService $certificateService, Certificate $certificate): RedirectResponse
    {
        $data = $request->validated();

        // If a new file was uploaded, attach it. Otherwise remove the key
        // so we don't attempt to write NULL into a non-nullable column.
        if ($request->hasFile('document_scan_copy')) {
            $data['document_scan_copy'] = $request->file('document_scan_copy');
        } else {
            if (array_key_exists('document_scan_copy', $data) && empty($data['document_scan_copy'])) {
                unset($data['document_scan_copy']);
            }
        }

        try {
            $certificateService->update($certificate->id, $data);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
            return back()->withErrors(['database' => 'حدث خطأ أثناء حفظ البيانات. الرجاء التحقق من الحقول وإعادة المحاولة.'])->withInput();
        }

        // Delegate notification logic to the service (keeps controller slim)
        $certificateService->notifyUserStatus($certificate, $data);

        return redirect()->route('admin.certificates.index');
    }

    public function destroy(CertificateService $certificateService, Certificate $certificate): RedirectResponse
    {
        $certificateService->delete($certificate->id);

        return redirect()->route('admin.certificates.index');
    }

    public function viewDocument(Certificate $certificate, CertificateService $certificateService): StreamedResponse
    {
        return $certificateService->streamDocument($certificate, false);
    }

    public function downloadDocument(Certificate $certificate, CertificateService $certificateService): StreamedResponse
    {
        return $certificateService->streamDocument($certificate, true);
    }

    // NOTE: user selection is provided inline in create/edit methods to follow the
    // same pattern used elsewhere (e.g. governorates for selection). This avoids
    // having a shared helper method and keeps the logic local to the controller
    // action. If you prefer a reusable method, we can restore it.
}
