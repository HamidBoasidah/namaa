<?php

namespace App\Services;

use App\Repositories\ConsultantRepository;
use App\Models\Consultant;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConsultantService
{
    protected ConsultantRepository $consultants;

    public function __construct(ConsultantRepository $consultants)
    {
        $this->consultants = $consultants;
    }

    public function all(array $with = [])
    {
        return $this->consultants->all($with);
    }

    public function paginate(int $perPage = 15, array $with = [])
    {
        return $this->consultants->paginate($perPage, $with);
    }

    public function find($id, array $with = [])
    {
        return $this->consultants->findOrFail($id, $with);
    }

    public function create(array $attributes)
    {
        // منطق إضافي قبل الإنشاء (إن وجد لاحقًا)
        return $this->consultants->create($attributes);
    }

    public function update($id, array $attributes)
    {
        return $this->consultants->update($id, $attributes);
    }

    public function delete($id)
    {
        return $this->consultants->delete($id);
    }

    public function activate($id)
    {
        return $this->consultants->activate($id);
    }

    public function deactivate($id)
    {
        return $this->consultants->deactivate($id);
    }

    /**
     * جلب المستشارين حسب الفئة
     */
    public function getByCategory(int $categoryId, int $perPage = 10): LengthAwarePaginator
    {
        // التحقق من وجود الفئة
        Category::findOrFail($categoryId);

        return Consultant::where('is_active', true)
            ->whereHas('service', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with(['user:id,first_name,last_name,avatar', 'service.category:id,name'])
            ->paginate($perPage);
    }

    /**
     * جلب المستشارين للجوال مع خيارات الترتيب
     */
    public function getForMobile(?string $sortBy = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Consultant::where('is_active', true)
            ->with(['user:id,first_name,last_name,avatar', 'service.category:id,name']);

        // تطبيق الترتيب
        switch ($sortBy) {
            case 'experience':
                $query->orderByDesc('years_of_experience');
                break;
            case 'rating':
                $query->orderByDesc('rating_avg');
                break;
            case 'reviews':
                $query->orderByDesc('ratings_count');
                break;
            default:
                $query->latest();
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * جلب فئات خدمات المستشار (بدون تكرار)
     */
    public function getServiceCategories(Consultant $consultant): array
    {
        // تحميل العلاقة إذا لم تكن محملة
        if (!$consultant->relationLoaded('service')) {
            $consultant->load('service.category:id,name');
        }

        $categories = [];
        
        if ($consultant->service && $consultant->service->category) {
            $categories[] = $consultant->service->category->name;
        }

        return array_unique($categories);
    }

    /**
     * Expose query builder for controllers
     */
    public function query(?array $with = null): Builder
    {
        return $this->consultants->query($with);
    }
}
