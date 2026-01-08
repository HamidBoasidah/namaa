<?php

namespace App\Services;

use App\Repositories\ConsultationTypeRepository;
use App\Models\ConsultationType;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;


class ConsultationTypeService
{
    protected ConsultationTypeRepository $items;

    public function __construct(ConsultationTypeRepository $items)
    {
        $this->items = $items;
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
}
