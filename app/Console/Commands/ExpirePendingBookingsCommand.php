<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:expire-pending';

    /**
     * The console command description.
     */
    protected $description = 'تحديث حالة الحجوزات المعلقة المنتهية الصلاحية إلى منتهية';

    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        parent::__construct();
        $this->bookingService = $bookingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredCount = $this->bookingService->expireOldPending();

        if ($expiredCount > 0) {
            $this->info("تم تحديث {$expiredCount} حجز معلق إلى منتهي الصلاحية.");
            Log::info("Expired {$expiredCount} pending bookings.");
        } else {
            $this->info('لا توجد حجوزات معلقة منتهية الصلاحية.');
        }

        return Command::SUCCESS;
    }
}
