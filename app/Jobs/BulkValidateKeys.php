<?php

namespace App\Jobs;

use App\Services\License\PidKeyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkValidateKeys implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 600;

    protected $keys;
    protected $productId;
    protected $adminId;

    public function __construct(array $keys, int $productId, int $adminId)
    {
        $this->keys = $keys;
        $this->productId = $productId;
        $this->adminId = $adminId;
    }

    public function handle(PidKeyService $pidKeyService): void
    {
        Log::info('Starting bulk validation job', [
            'key_count' => count($this->keys),
            'product_id' => $this->productId,
            'admin_id' => $this->adminId,
        ]);
        
        $result = $pidKeyService->bulkValidateFromText(
            implode("\n", $this->keys)
        );
        
        Log::info('Bulk validation job completed', [
            'total_keys' => $result['total_keys'] ?? 0,
            'valid_count' => $result['valid_count'] ?? 0,
            'invalid_count' => $result['invalid_count'] ?? 0,
            'product_id' => $this->productId,
        ]);
        
        // Store results in cache or database for admin to view
        cache()->put(
            "bulk_validation_result_{$this->adminId}",
            $result,
            now()->addHours(1)
        );
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk validation job failed', [
            'error' => $exception->getMessage(),
            'product_id' => $this->productId,
            'admin_id' => $this->adminId,
        ]);
    }
}