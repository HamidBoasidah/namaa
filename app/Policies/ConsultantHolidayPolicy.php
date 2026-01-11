<?php

namespace App\Policies;

use App\Models\ConsultantHoliday;
use App\Models\Consultant;
use App\Models\User;

class ConsultantHolidayPolicy
{
    /**
     * Get consultant for user
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * المستخدم يمكنه رؤية إجازاته فقط
     */
    public function view(User $user, ConsultantHoliday $holiday): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $holiday->consultant_id;
    }

    /**
     * المستخدم يمكنه إنشاء إجازة لنفسه فقط
     */
    public function create(User $user): bool
    {
        return $user->user_type === 'consultant' && $this->getConsultant($user) !== null;
    }

    /**
     * المستخدم يمكنه تعديل إجازاته فقط
     */
    public function update(User $user, ConsultantHoliday $holiday): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $holiday->consultant_id;
    }

    /**
     * المستخدم يمكنه حذف إجازاته فقط
     */
    public function delete(User $user, ConsultantHoliday $holiday): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $holiday->consultant_id;
    }
}
