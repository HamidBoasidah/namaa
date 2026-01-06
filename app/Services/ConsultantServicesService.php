<?php

namespace App\Services;

use App\Repositories\ConsultantServiceRepository;
use Illuminate\Support\Facades\DB;

class ConsultantServicesService
{
    protected ConsultantServiceRepository $services;

    public function __construct(ConsultantServiceRepository $services)
    {
        $this->services = $services;
    }

    public function all(?array $with = null)
    {
        return $this->services->all($with);
    }

    public function paginate(int $perPage = 15, ?array $with = null)
    {
        return $this->services->paginate($perPage, $with);
    }

    public function find(int|string $id, ?array $with = null)
    {
        return $this->services->findOrFail($id, $with);
    }

    public function create(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {
            $tags = $attributes['tags'] ?? null;
            unset($attributes['tags']);

            $service = $this->services->create($attributes);

            if ($tags !== null) {
                $service->tags()->sync($tags);
            }

            return $service;
        });
    }

    public function update(int|string $id, array $attributes)
    {
        return DB::transaction(function () use ($id, $attributes) {
            $hasTagsKey = array_key_exists('tags', $attributes);
            $tags = $hasTagsKey ? ($attributes['tags'] ?? []) : null;
            unset($attributes['tags']);

            $service = $this->services->update($id, $attributes);

            if ($service && $hasTagsKey) {
                $service->tags()->sync($tags);
            }

            return $service;
        });
    }

    public function delete(int|string $id)
    {
        return $this->services->delete($id);
    }

    public function activate(int|string $id)
    {
        return $this->services->activate($id);
    }

    public function deactivate(int|string $id)
    {
        return $this->services->deactivate($id);
    }
}
