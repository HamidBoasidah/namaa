<?php

namespace App\Repositories;

use App\Models\LandingSection;
use Illuminate\Database\Eloquent\Collection;

class LandingSectionRepository
{
    public function getBySectionPageId(int $landingPageId): Collection
    {
        return LandingSection::with('items')
            ->where('landing_page_id', $landingPageId)
            ->orderBy('order')
            ->get();
    }

    public function findById(int $id): ?LandingSection
    {
        return LandingSection::with('items')->find($id);
    }

    public function create(array $data): LandingSection
    {
        return LandingSection::create($data);
    }

    public function update(LandingSection $section, array $data): bool
    {
        return $section->update($data);
    }

    public function delete(LandingSection $section): bool
    {
        return $section->delete();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            LandingSection::where('id', $id)->update(['order' => $index]);
        }
    }
}
