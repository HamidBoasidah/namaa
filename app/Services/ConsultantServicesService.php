<?php

namespace App\Services;

use App\Models\ConsultantService;
use App\Models\ConsultantServiceDetail;
use App\Repositories\ConsultantServiceRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConsultantServicesService
{
    protected ConsultantServiceRepository $services;

    /**
     * The storage directory for service icons.
     */
    protected const ICON_STORAGE_PATH = 'consultant-services/icons';

    public function __construct(ConsultantServiceRepository $services)
    {
        $this->services = $services;
    }

    /**
     * Upload an icon file to storage.
     *
     * @param UploadedFile $file The uploaded icon file
     * @return string The stored file path
     */
    public function uploadIcon(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs(self::ICON_STORAGE_PATH, $filename, 'public');
    }

    /**
     * Delete an icon file from storage.
     *
     * @param string $path The icon path to delete
     * @return bool True if deleted successfully
     */
    public function deleteIcon(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
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
            
            // Handle icon upload
            if (isset($attributes['icon']) && $attributes['icon'] instanceof UploadedFile) {
                $attributes['icon_path'] = $this->uploadIcon($attributes['icon']);
                unset($attributes['icon']);
            }
            
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
            
            // Handle remove_icon flag
            if (isset($attributes['remove_icon']) && $attributes['remove_icon']) {
                $service = $this->services->findOrFail($id);
                if ($service->icon_path) {
                    $this->deleteIcon($service->icon_path);
                }
                $attributes['icon_path'] = null;
                unset($attributes['remove_icon']);
            }
            
            // Handle icon upload (replacement)
            if (isset($attributes['icon']) && $attributes['icon'] instanceof UploadedFile) {
                $service = $this->services->findOrFail($id);
                if ($service->icon_path) {
                    $this->deleteIcon($service->icon_path);
                }
                $attributes['icon_path'] = $this->uploadIcon($attributes['icon']);
                unset($attributes['icon']);
            }
            
            unset($attributes['tags'], $attributes['includes'], $attributes['target_audience'], $attributes['deliverables'], $attributes['remove_icon']);

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
        $service = $this->services->findOrFail($id);
        
        // Delete icon file if exists
        if ($service->icon_path) {
            $this->deleteIcon($service->icon_path);
        }
        
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

    /**
     * Get paginated list of active services for public API.
     * Returns only services where is_active = true.
     *
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getActiveServices(int $perPage = 10)
    {
        return ConsultantService::where('is_active', true)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get service details by ID for public API.
     * Returns the service only if it exists and is active.
     * Loads consultant with user, consultationType, and experiences relations.
     *
     * @param int $id Service ID
     * @return ConsultantService
     * @throws NotFoundHttpException If service not found or not active
     */
    public function getServiceDetails(int $id): ConsultantService
    {
        $service = ConsultantService::with([
            'consultant.user',
            'consultant.consultationType',
            'consultant.experiences',
            'includes',
            'targetAudience',
            'deliverables',
        ])
            ->where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            throw new NotFoundHttpException('الخدمة غير موجودة أو غير نشطة');
        }

        return $service;
    }
}
