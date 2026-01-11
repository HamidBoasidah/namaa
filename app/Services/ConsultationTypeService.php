<?php

namespace App\Services;

use App\Repositories\ConsultationTypeRepository;
use App\Models\ConsultationType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use App\Services\SVGIconService;


class ConsultationTypeService
{
    protected ConsultationTypeRepository $items;
    protected SVGIconService $svgIconService;
    protected string $iconStoragePath = 'consultation-type-icons';

    public function __construct(ConsultationTypeRepository $items, SVGIconService $svgIconService)
    {
        $this->items = $items;
        $this->svgIconService = $svgIconService;
    }

    public function all(array $with = [])
    {
        return $this->items->all($with);
    }

    public function paginate(int $perPage = 15, array $with = [])
    {
        return $this->items->paginate($perPage, $with);
    }

    public function query(?array $with = null): Builder
    {
        return $this->items->query($with);
    }

    public function find($id, array $with = [])
    {
        return $this->items->findOrFail($id, $with);
    }

    public function create(array $attributes)
    {
        if (empty($attributes['slug']) && ! empty($attributes['name'])) {
            $attributes['slug'] = $this->makeUniqueSlug($attributes['name']);
        }

        return $this->items->create($attributes);
    }

    public function update($id, array $attributes)
    {
        if (! empty($attributes['name'])) {
            $attributes['slug'] = $this->makeUniqueSlug($attributes['name'], $id);
        }

        return $this->items->update($id, $attributes);
    }

    protected function makeUniqueSlug(string $name, $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (ConsultationType::where('slug', $slug)->when($ignoreId, function ($q) use ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        })->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }

    public function delete($id)
    {
        $item = $this->items->findOrFail($id);

        if ($item->icon_path) {
            $this->svgIconService->deleteIcon($item->icon_path);
        }

        return $this->items->delete($id);
    }

    public function activate($id)
    {
        return $this->items->activate($id);
    }

    public function deactivate($id)
    {
        return $this->items->deactivate($id);
    }

    /**
     * Upload an icon for a consultation type
     */
    public function uploadIcon(int $consultationTypeId, UploadedFile $iconFile): ConsultationType
    {
        $consultationType = $this->items->findOrFail($consultationTypeId);

        if ($consultationType->icon_path) {
            $this->svgIconService->deleteIcon($consultationType->icon_path);
        }

        $iconPath = $this->svgIconService->uploadIcon($iconFile, $consultationTypeId, 'consultation_type', $this->iconStoragePath);

        return $this->items->update($consultationTypeId, [
            'icon_path' => $iconPath,
        ]);
    }

    /**
     * Remove icon for a consultation type
     */
    public function removeIcon(int $consultationTypeId): ConsultationType
    {
        $consultationType = $this->items->findOrFail($consultationTypeId);

        if ($consultationType->icon_path) {
            $this->svgIconService->deleteIcon($consultationType->icon_path);

            return $this->items->update($consultationTypeId, [
                'icon_path' => null,
            ]);
        }

        return $consultationType;
    }
}
