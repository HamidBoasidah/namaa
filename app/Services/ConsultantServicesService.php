<?php

namespace App\Services;

use App\Models\ConsultantServiceDetail;
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
            $includes = $attributes['includes'] ?? [];
            $targetAudience = $attributes['target_audience'] ?? [];
            $deliverables = $attributes['deliverables'] ?? [];
            
            unset($attributes['tags'], $attributes['includes'], $attributes['target_audience'], $attributes['deliverables']);

            $service = $this->services->create($attributes);

            if ($tags !== null) {
                $service->tags()->sync($tags);
            }

            // حفظ تفاصيل الخدمة
            $this->syncDetails($service->id, 'includes', $includes);
            $this->syncDetails($service->id, 'target_audience', $targetAudience);
            $this->syncDetails($service->id, 'deliverables', $deliverables);

            return $service;
        });
    }

    public function update(int|string $id, array $attributes)
    {
        return DB::transaction(function () use ($id, $attributes) {
            $hasTagsKey = array_key_exists('tags', $attributes);
            $tags = $hasTagsKey ? ($attributes['tags'] ?? []) : null;
            
            $includes = $attributes['includes'] ?? null;
            $targetAudience = $attributes['target_audience'] ?? null;
            $deliverables = $attributes['deliverables'] ?? null;
            
            unset($attributes['tags'], $attributes['includes'], $attributes['target_audience'], $attributes['deliverables']);

            $service = $this->services->update($id, $attributes);

            if ($service) {
                if ($hasTagsKey) {
                    $service->tags()->sync($tags);
                }

                // تحديث تفاصيل الخدمة
                if ($includes !== null) {
                    $this->syncDetails($service->id, 'includes', $includes);
                }
                if ($targetAudience !== null) {
                    $this->syncDetails($service->id, 'target_audience', $targetAudience);
                }
                if ($deliverables !== null) {
                    $this->syncDetails($service->id, 'deliverables', $deliverables);
                }
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

    /**
     * مزامنة تفاصيل الخدمة (حذف القديم وإضافة الجديد)
     */
    protected function syncDetails(int $serviceId, string $type, array $contents): void
    {
        // حذف التفاصيل القديمة من هذا النوع
        ConsultantServiceDetail::where('consultant_service_id', $serviceId)
            ->where('type', $type)
            ->delete();

        // إضافة التفاصيل الجديدة
        foreach ($contents as $index => $content) {
            if (!empty(trim($content))) {
                ConsultantServiceDetail::create([
                    'consultant_service_id' => $serviceId,
                    'type' => $type,
                    'content' => trim($content),
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
