<?php

namespace App\Policies;

use App\Models\ConsultantExperience;
use App\Models\Consultant;
use App\Models\User;

class ConsultantExperiencePolicy
{
    /**
     * Get consultant for user
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * المستخدم يمكنه رؤية خبراته فقط
     */
    public function view(User $user, ConsultantExperience $experience): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $experience->consultant_id;
    }

    /**
     * المستخدم يمكنه إنشاء خبرة لنفسه
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه تعديل خبراته فقط
     */
    public function update(User $user, ConsultantExperience $experience): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $experience->consultant_id;
    }

    /**
     * المستخدم يمكنه حذف خبراته فقط
     */
    public function delete(User $user, ConsultantExperience $experience): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $experience->consultant_id;
    }
}
