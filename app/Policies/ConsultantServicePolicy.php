<?php

namespace App\Policies;

use App\Models\ConsultantService;
use App\Models\Consultant;
use App\Models\User;

class ConsultantServicePolicy
{
    /**
     * Get consultant for user
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * المستخدم يمكنه عرض قائمة خدماته
     */
    public function viewAny(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه رؤية خدماته فقط
     */
    public function view(User $user, ConsultantService $service): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $service->consultant_id;
    }

    /**
     * المستخدم يمكنه إنشاء خدمة لنفسه فقط
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه تعديل خدماته فقط
     */
    public function update(User $user, ConsultantService $service): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $service->consultant_id;
    }

    /**
     * المستخدم يمكنه حذف خدماته فقط
     */
    public function delete(User $user, ConsultantService $service): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $service->consultant_id;
    }
}
