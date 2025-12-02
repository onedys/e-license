<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $productId;
    protected $currentStock;

    public function __construct(int $productId, int $currentStock)
    {
        $this->productId = $productId;
        $this->currentStock = $currentStock;
    }

    public function handle(): void
    {
        $product = Product::find($this->productId);
        
        if (!$product) {
            Log::error('Product not found for low stock alert', [
                'product_id' => $this->productId,
            ]);
            return;
        }
        
        // Get admin users
        $admins = User::where('is_admin', true)->get();
        
        foreach ($admins as $admin) {
            // Send notification (you would implement your notification system)
            $this->sendNotification($admin, $product, $this->currentStock);
        }
        
        Log::info('Low stock alert sent', [
            'product_id' => $this->productId,
            'product_name' => $product->name,
            'current_stock' => $this->currentStock,
            'admins_notified' => $admins->count(),
        ]);
    }
    
    private function sendNotification(User $admin, Product $product, int $currentStock): void
    {
        // Create in-app notification
        $admin->notifications()->create([
            'type' => 'low_stock_alert',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $currentStock,
                'message' => "Stok {$product->name} hampir habis: {$currentStock} lisensi tersisa.",
                'url' => route('admin.license-pool.index', ['product_id' => $product->id]),
            ],
        ]);
    }
}