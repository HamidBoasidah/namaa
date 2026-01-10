<?php

namespace App\Services;

use App\Repositories\ConsultantExperienceRepository;
use Illuminate\Support\Facades\DB;

class ConsultantExperienceService
{
    protected ConsultantExperienceRepository $experiences;

    public function __construct(ConsultantExperienceRepository $experiences)
    {
        $this->experiences = $experiences;
    }

    public function all(array $with = [])
    {
        return $this->experiences->all($with);
    }

    public function paginate(int $perPage = 15, array $with = [])
    {
        return $this->experiences->paginate($perPage, $with);
    }

    public function find($id, array $with = [])
    {
        return $this->experiences->findOrFail($id, $with);
    }

    public function create(array $attributes)
    {
        return $this->experiences->create($attributes);
    }

    public function update($id, array $attributes)
    {
        return $this->experiences->update($id, $attributes);
    }

    public function delete($id)
    {
        return $this->experiences->delete($id);
    }

    public function activate($id)
    {
        return $this->experiences->activate($id);
    }

    public function deactivate($id)
    {
        return $this->experiences->deactivate($id);
    }

    /**
     * Replace all experiences for a consultant with the provided list.
     */
    public function replaceForConsultant(int $consultantId, array $experiences): void
    {
        DB::transaction(function () use ($consultantId, $experiences) {
            // soft delete existing experiences
            $this->experiences->query([])->where('consultant_id', $consultantId)->delete();

            if (empty($experiences)) {
                return;
            }

            $now = now();

            $payload = array_map(function ($exp) use ($consultantId, $now) {
                return [
                    'consultant_id' => $consultantId,
                    'name' => $exp['name'],
                    'is_active' => isset($exp['is_active']) ? (bool) $exp['is_active'] : true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $experiences);

            $this->experiences->query([])->insert($payload);
        });
    }
}
