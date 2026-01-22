<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Consultant;
use App\Models\ConsultantExperience;
use App\Models\ConsultantHoliday;
use App\Models\ConsultantService;
use App\Models\ConsultantWorkingHour;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Review;
use App\Policies\AttachmentPolicy;
use App\Policies\BookingPolicy;
use App\Policies\ConsultantServicePolicy;
use App\Policies\CertificatePolicy;
use App\Policies\ConsultantExperiencePolicy;
use App\Policies\ConsultantHolidayPolicy;
use App\Policies\ConsultantWorkingHourPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);
        Gate::policy(ConsultantExperience::class, ConsultantExperiencePolicy::class);
        Gate::policy(ConsultantWorkingHour::class, ConsultantWorkingHourPolicy::class);
        Gate::policy(ConsultantHoliday::class, ConsultantHolidayPolicy::class);
        Gate::policy(ConsultantService::class, ConsultantServicePolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(MessageAttachment::class, AttachmentPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
