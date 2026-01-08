<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\ConsultantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected UserRepository $users;
    protected ConsultantService $consultantService;
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
        // inject consultant service when resolved from container
        $this->consultantService = app(ConsultantService::class);
    }

    public function all(array $with = [])
    {
        return $this->users->all($with);
    }

    public function paginate(int $perPage = 15, array $with = [])
    {
        return $this->users->paginate($perPage, $with);
    }

    public function find($id, array $with = [])
    {
        return $this->users->findOrFail($id, $with);
    }

    public function create(array $attributes)
    {
        // Wrap in DB transaction: create user, then create consultant record if needed
        return DB::transaction(function () use ($attributes) {
            // hash password if present
            if (array_key_exists('password', $attributes) && !empty($attributes['password'])) {
                $attributes['password'] = Hash::make($attributes['password']);
            }

            $user = $this->users->create($attributes);

            // if user is a consultant, create a consultant row linked to the user
            if (isset($attributes['user_type']) && $attributes['user_type'] === 'consultant') {
                $consultantData = [
                    'user_id' => $user->id,
                    'consultation_type_id' => $attributes['consultation_type_id'] ?? null,
                    'years_of_experience' => $attributes['years_of_experience'] ?? null,
                ];

                $this->consultantService->create($consultantData);
            }

            return $user;
        });
    }

    public function update($id, array $attributes)
    {
        // لا تقم بتحديث كلمة المرور إذا لم يتم إرسالها
        if (array_key_exists('password', $attributes) && empty($attributes['password'])) {
            unset($attributes['password']);
        }
        return $this->users->update($id, $attributes);
    }

    public function delete($id)
    {
        return $this->users->delete($id);
    }

    public function activate($id)
    {
        return $this->users->activate($id);
    }

    public function deactivate($id)
    {
        return $this->users->deactivate($id);
    }
}
