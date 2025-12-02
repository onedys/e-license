<?php

namespace App\Console\Commands;

use App\Services\Notification\AdminAlertService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'license:check-stock';
    protected $description = 'Check for low stock and send notifications';

    public function handle()
    {
        $this->info('Checking for low stock...');
        
        $adminAlertService = new AdminAlertService();
        $adminAlertService->checkAndSendLowStockAlerts();
        
        $this->info('Low stock check completed.');
        
        return Command::SUCCESS;
    }
}