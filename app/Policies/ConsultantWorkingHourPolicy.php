<?php

namespace App\Policies;

use App\Models\ConsultantWorkingHour;
use App\Models\Consultant;
use App\Models\User;

class ConsultantWorkingHourPolicy
{
    /**
     * Get consultant for user
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * المستخدم يمكنه رؤية قائمة ساعات عمله
     */
    public function viewAny(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه رؤية ساعات عمله فقط
     */
    public function view(User $user, ConsultantWorkingHour $workingHour): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $workingHour->consultant_id;
    }

    /**
     * المستخدم يمكنه إنشاء ساعة عمل لنفسه فقط
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه تعديل ساعات عمله فقط
     */
    public function update(User $user, ConsultantWorkingHour $workingHour): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $workingHour->consultant_id;
    }

    /**
     * المستخدم يمكنه حذف ساعات عمله فقط
     */
    public function delete(User $user, ConsultantWorkingHour $workingHour): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $workingHour->consultant_id;
    }

    /**
     * المستخدم يمكنه تفعيل ساعات عمله فقط
     */
    public function activate(User $user, ConsultantWorkingHour $workingHour): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $workingHour->consultant_id;
    }

    /**
     * المستخدم يمكنه تعطيل ساعات عمله فقط
     */
    public function deactivate(User $user, ConsultantWorkingHour $workingHour): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $workingHour->consultant_id;
    }
}
