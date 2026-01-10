<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\Consultant;
use App\Models\User;

class CertificatePolicy
{
    /**
     * Get consultant for user
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * المستخدم يمكنه رؤية شهاداته فقط
     */
    public function view(User $user, Certificate $certificate): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $certificate->consultant_id;
    }

    /**
     * المستخدم يمكنه إنشاء شهادة لنفسه
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه تعديل شهاداته فقط
     */
    public function update(User $user, Certificate $certificate): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $certificate->consultant_id;
    }

    /**
     * المستخدم يمكنه حذف شهاداته فقط
     */
    public function delete(User $user, Certificate $certificate): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $certificate->consultant_id;
    }
}
