<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckLowStock::class,
        Commands\ValidateLicensePool::class,
        Commands\CleanupLogs::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Daily license validation
        $schedule->command('license:validate-pool')
                 ->dailyAt('02:00')
                 ->timezone('Asia/Jakarta');
        
        // Daily log cleanup
        $schedule->command('logs:cleanup --days=30')
                 ->dailyAt('03:00')
                 ->timezone('Asia/Jakarta');
        
        // Hourly low stock check
        $schedule->call(function () {
            $products = \App\Models\Product::all();
            
            foreach ($products as $product) {
                $stock = \App\Models\LicensePool::where('product_id', $product->id)
                    ->where('status', 'active')
                    ->count();
                
                if ($stock < 10) {
                    \App\Jobs\SendLowStockAlert::dispatch($product->id, $stock);
                }
            }
        })->hourly();
        
        // Queue worker restart
        $schedule->command('queue:restart')
                 ->hourly();
        
        // Prune old Sanctum tokens
        $schedule->command('sanctum:prune-expired --hours=24')
                 ->daily();
        
        // Check pending warranty every 30 minutes
        $schedule->call(function () {
            $adminAlertService = new \App\Services\Notification\AdminAlertService();
            $adminAlertService->sendPendingWarrantyAlert();
        })->everyThirtyMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}