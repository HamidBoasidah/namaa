<?php

namespace App\Console\Commands;

use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Services\RatingsCalculatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecalculateAllRatings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ratings:recalculate-all {--chunk=100 : Number of records to process per chunk}';

    /**
     * The console command description.
     */
    protected $description = 'إعادة حساب جميع التقييمات للمستشارين والخدمات';

    /**
     * The ratings calculator service
     */
    protected RatingsCalculatorService $ratingsCalculator;

    /**
     * Create a new command instance
     */
    public function __construct(RatingsCalculatorService $ratingsCalculator)
    {
        parent::__construct();
        $this->ratingsCalculator = $ratingsCalculator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $totalConsultants = 0;
        $totalServices = 0;

        $this->info('بدء عملية إعادة حساب جميع التقييمات...');

        try {
            // Recalculate consultant ratings
            $this->info('إعادة حساب تقييمات المستشارين...');
            $consultantCount = Consultant::count();
            
            if ($consultantCount > 0) {
                $progressBar = $this->output->createProgressBar($consultantCount);
                $progressBar->start();

                Consultant::chunk($chunkSize, function ($consultants) use (&$totalConsultants, $progressBar) {
                    foreach ($consultants as $consultant) {
                        try {
                            $this->ratingsCalculator->updateConsultantRatings($consultant->id);
                            $totalConsultants++;
                            $progressBar->advance();
                        } catch (\Exception $e) {
                            // Log error but continue processing
                            Log::error('Failed to update consultant ratings', [
                                'consultant_id' => $consultant->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });

                $progressBar->finish();
                $this->newLine(2);
            }

            $this->info("تم تحديث تقييمات {$totalConsultants} مستشار.");

            // Recalculate service ratings
            $this->info('إعادة حساب تقييمات الخدمات...');
            $serviceCount = ConsultantService::count();
            
            if ($serviceCount > 0) {
                $progressBar = $this->output->createProgressBar($serviceCount);
                $progressBar->start();

                ConsultantService::chunk($chunkSize, function ($services) use (&$totalServices, $progressBar) {
                    foreach ($services as $service) {
                        try {
                            $this->ratingsCalculator->updateServiceRatings($service->id);
                            $totalServices++;
                            $progressBar->advance();
                        } catch (\Exception $e) {
                            // Log error but continue processing
                            Log::error('Failed to update service ratings', [
                                'service_id' => $service->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });

                $progressBar->finish();
                $this->newLine(2);
            }

            $this->info("تم تحديث تقييمات {$totalServices} خدمة.");
            $this->info('اكتملت عملية إعادة حساب التقييمات بنجاح!');

            Log::info('Recalculated all ratings', [
                'total_consultants' => $totalConsultants,
                'total_services' => $totalServices,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء إعادة حساب التقييمات: ' . $e->getMessage());
            
            Log::error('Failed to recalculate all ratings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
