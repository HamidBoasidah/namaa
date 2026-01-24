<?php

namespace App\Console\Commands;

use App\Models\ConsultantService;
use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillReviewServiceIds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reviews:backfill-service-ids {--chunk=100 : Number of reviews to process per chunk}';

    /**
     * The console command description.
     */
    protected $description = 'ملء consultant_service_id للتقييمات الموجودة من بيانات الحجز';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $totalProcessed = 0;
        $totalUpdated = 0;

        $this->info('بدء عملية ملء consultant_service_id للتقييمات...');

        try {
            // Get total count for progress bar
            $totalReviews = Review::whereNull('consultant_service_id')->count();
            
            if ($totalReviews === 0) {
                $this->info('لا توجد تقييمات تحتاج إلى تحديث.');
                return Command::SUCCESS;
            }

            $this->info("عدد التقييمات التي تحتاج إلى تحديث: {$totalReviews}");
            $progressBar = $this->output->createProgressBar($totalReviews);
            $progressBar->start();

            // Process reviews in chunks for performance
            Review::whereNull('consultant_service_id')
                ->with('booking.bookable')
                ->chunk($chunkSize, function ($reviews) use (&$totalProcessed, &$totalUpdated, $progressBar) {
                    foreach ($reviews as $review) {
                        $totalProcessed++;
                        
                        // Check if booking exists and has a bookable
                        if ($review->booking && $review->booking->bookable) {
                            $bookable = $review->booking->bookable;
                            
                            // If bookable is ConsultantService, update the review
                            if ($bookable instanceof ConsultantService) {
                                DB::table('reviews')
                                    ->where('id', $review->id)
                                    ->update(['consultant_service_id' => $bookable->id]);
                                
                                $totalUpdated++;
                            }
                        }
                        
                        $progressBar->advance();
                    }
                });

            $progressBar->finish();
            $this->newLine(2);

            $this->info("تمت معالجة {$totalProcessed} تقييم.");
            $this->info("تم تحديث {$totalUpdated} تقييم بـ consultant_service_id.");
            
            Log::info('Backfilled review service IDs', [
                'total_processed' => $totalProcessed,
                'total_updated' => $totalUpdated,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء معالجة التقييمات: ' . $e->getMessage());
            
            Log::error('Failed to backfill review service IDs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
