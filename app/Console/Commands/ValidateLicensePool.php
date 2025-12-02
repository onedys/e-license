<?php

namespace App\Console\Commands;

use App\Models\LicensePool;
use App\Services\License\PidKeyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ValidateLicensePool extends Command
{
    protected $signature = 'license:validate-pool';
    protected $description = 'Validate all active licenses in the pool';

    protected $pidKeyService;

    public function __construct(PidKeyService $pidKeyService)
    {
        parent::__construct();
        $this->pidKeyService = $pidKeyService;
    }

    public function handle(): void
    {
        $this->info('Starting license pool validation...');
        
        $licenses = LicensePool::where('status', 'active')->get();
        $total = $licenses->count();
        
        $this->info("Found {$total} active licenses to validate.");
        
        $validCount = 0;
        $blockedCount = 0;
        $errorCount = 0;
        
        $bar = $this->output->createProgressBar($total);
        
        foreach ($licenses as $license) {
            try {
                $validation = $this->pidKeyService->validateKey(
                    $license->getPlainAttribute('license_key')
                );
                
                if ($validation['is_valid']) {
                    $license->update([
                        'errorcode' => $validation['errorcode'],
                        'last_validated_at' => now(),
                        'validation_count' => $license->validation_count + 1,
                    ]);
                    $validCount++;
                } else {
                    $license->update([
                        'errorcode' => $validation['errorcode'],
                        'status' => 'blocked',
                        'last_validated_at' => now(),
                        'validation_count' => $license->validation_count + 1,
                    ]);
                    $blockedCount++;
                    
                    Log::warning('License blocked during validation', [
                        'license_pool_id' => $license->id,
                        'errorcode' => $validation['errorcode'],
                    ]);
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('License validation failed', [
                    'license_pool_id' => $license->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $bar->advance();
            
            sleep(1);
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Validation completed!");
        $this->info("Valid: {$validCount}, Blocked: {$blockedCount}, Errors: {$errorCount}");
        
        Log::info('License pool validation completed', [
            'total' => $total,
            'valid' => $validCount,
            'blocked' => $blockedCount,
            'errors' => $errorCount,
        ]);
    }
}