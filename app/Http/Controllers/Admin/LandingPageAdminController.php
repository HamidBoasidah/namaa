<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LandingPageService;
use App\Repositories\LandingPageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageAdminController extends Controller
{
    public function __construct(
        private LandingPageService $landingPageService,
        private LandingPageRepository $landingPageRepository
    ) {}

    public function index(): Response
    {
        $pages = $this->landingPageRepository->getAll();

        return Inertia::render('Admin/Landing/Index', [
            'pages' => $pages,
        ]);
    }

    public function show(int $id): Response
    {
        $page = $this->landingPageRepository->findById($id);

        if (!$page) {
            abort(404);
        }

        return Inertia::render('Admin/Landing/Show', [
            'page' => $page,
            'sections' => $page->sections()->with('items')->orderBy('order')->get(),
        ]);
    }

    public function storeSection(Request $request, int $pageId): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
            'background_color' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('landing/sections', 'public');
        }

        $this->landingPageService->createSection($pageId, $validated);

        return redirect()->back()->with('success', 'تم إضافة القسم بنجاح');
    }

    public function updateSection(Request $request, int $sectionId): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'settings' => 'nullable|string',
            'background_color' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle settings JSON string
        if (isset($validated['settings']) && is_string($validated['settings'])) {
            $validated['settings'] = json_decode($validated['settings'], true) ?: [];
        }

        // Handle is_active for form data
        if (!$request->has('is_active')) {
            $validated['is_active'] = false;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('landing/sections', 'public');
        }

        $this->landingPageService->updateSection($sectionId, $validated);

        return redirect()->back()->with('success', 'تم تحديث القسم بنجاح');
    }

    public function deleteSection(int $sectionId): RedirectResponse
    {
        $this->landingPageService->deleteSection($sectionId);

        return redirect()->back()->with('success', 'تم حذف القسم بنجاح');
    }

    public function reorderSections(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
        ]);

        $this->landingPageService->reorderSections($validated['order']);

        return redirect()->back()->with('success', 'تم تحديث الترتيب بنجاح');
    }

    public function storeSectionItem(Request $request, int $sectionId): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'link_text' => 'nullable|string',
            'background_color' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('landing/items', 'public');
        }

        $this->landingPageService->createSectionItem($sectionId, $validated);

        return redirect()->back()->with('success', 'تم إضافة العنصر بنجاح');
    }

    public function updateSectionItem(Request $request, int $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'image' => 'nullable|image|max:10240',
            'icon' => 'nullable|string',
            'link' => 'nullable|string',
            'link_text' => 'nullable|string',
            'background_color' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle is_active for form data
        if (!$request->has('is_active')) {
            $validated['is_active'] = false;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('landing/items', 'public');
        }

        $this->landingPageService->updateSectionItem($itemId, $validated);

        return redirect()->back()->with('success', 'تم تحديث العنصر بنجاح');
    }

    public function deleteSectionItem(int $itemId): RedirectResponse
    {
        $this->landingPageService->deleteSectionItem($itemId);

        return redirect()->back()->with('success', 'تم حذف العنصر بنجاح');
    }

    public function reorderSectionItems(Request $request, int $sectionId): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
        ]);

        $this->landingPageService->reorderSectionItems($sectionId, $validated['order']);

        return redirect()->back()->with('success', 'تم تحديث الترتيب بنجاح');
    }
}
