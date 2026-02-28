<?php

namespace App\Services;

use App\Models\LandingPage;
use App\Models\LandingSection;
use App\Models\LandingSectionItem;
use App\Repositories\LandingPageRepository;
use App\Repositories\LandingSectionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandingPageService
{
    public function __construct(
        private LandingPageRepository $landingPageRepository,
        private LandingSectionRepository $sectionRepository
    ) {}

    public function getLandingPageData(string $slug = 'home'): ?array
    {
        $page = $this->landingPageRepository->getActiveBySlug($slug);
        
        if (!$page) {
            return null;
        }

        return [
            'page' => $page,
            'sections' => $page->activeSections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'type' => $section->type,
                    'title' => $section->title,
                    'subtitle' => $section->subtitle,
                    'description' => $section->description,
                    'content' => $section->content,
                    'settings' => $section->settings,
                    'background_color' => $section->background_color,
                    'image' => $section->image,
                    'items' => $section->activeItems,
                ];
            }),
        ];
    }

    public function createLandingPage(array $data): LandingPage
    {
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $this->landingPageRepository->create($data);
    }

    public function updateLandingPage(int $id, array $data): bool
    {
        $page = $this->landingPageRepository->findById($id);
        
        if (!$page) {
            return false;
        }

        return $this->landingPageRepository->update($page, $data);
    }

    public function createSection(int $landingPageId, array $data): LandingSection
    {
        $data['landing_page_id'] = $landingPageId;
        
        // Get max order
        $maxOrder = LandingSection::where('landing_page_id', $landingPageId)->max('order');
        $data['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        return $this->sectionRepository->create($data);
    }

    public function updateSection(int $sectionId, array $data): bool
    {
        $section = $this->sectionRepository->findById($sectionId);
        
        if (!$section) {
            return false;
        }

        return $this->sectionRepository->update($section, $data);
    }

    public function deleteSection(int $sectionId): bool
    {
        $section = $this->sectionRepository->findById($sectionId);
        
        if (!$section) {
            return false;
        }

        return $this->sectionRepository->delete($section);
    }

    public function createSectionItem(int $sectionId, array $data): LandingSectionItem
    {
        $data['landing_section_id'] = $sectionId;
        
        // Get max order
        $maxOrder = LandingSectionItem::where('landing_section_id', $sectionId)->max('order');
        $data['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        return LandingSectionItem::create($data);
    }

    public function updateSectionItem(int $itemId, array $data): bool
    {
        $item = LandingSectionItem::find($itemId);
        
        if (!$item) {
            return false;
        }

        return $item->update($data);
    }

    public function deleteSectionItem(int $itemId): bool
    {
        $item = LandingSectionItem::find($itemId);
        
        if (!$item) {
            return false;
        }

        return $item->delete();
    }

    public function reorderSections(array $order): void
    {
        $this->sectionRepository->reorder($order);
    }

    public function reorderSectionItems(int $sectionId, array $order): void
    {
        foreach ($order as $index => $id) {
            LandingSectionItem::where('id', $id)
                ->where('landing_section_id', $sectionId)
                ->update(['order' => $index]);
        }
    }
}
