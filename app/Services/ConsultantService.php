<?php

namespace App\Services;

use App\Repositories\ConsultantRepository;

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
}
