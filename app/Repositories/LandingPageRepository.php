<?php

namespace App\Repositories;

use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Collection;

class LandingPageRepository
{
    public function getActiveBySlug(string $slug): ?LandingPage
    {
        return LandingPage::with(['activeSections.activeItems'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function getAll(): Collection
    {
        return LandingPage::with('sections')->orderBy('created_at', 'desc')->get();
    }

    public function findById(int $id): ?LandingPage
    {
        return LandingPage::with(['sections.items'])->find($id);
    }

    public function create(array $data): LandingPage
    {
        return LandingPage::create($data);
    }

    public function update(LandingPage $landingPage, array $data): bool
    {
        return $landingPage->update($data);
    }

    public function delete(LandingPage $landingPage): bool
    {
        return $landingPage->delete();
    }
}
