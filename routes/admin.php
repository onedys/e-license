<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicensePoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/system-info', [DashboardController::class, 'systemInfo'])->name('system.info');
    
    Route::prefix('license-pool')->name('license-pool.')->group(function () {
        Route::get('/', [LicensePoolController::class, 'index'])->name('index');
        Route::get('/create', [LicensePoolController::class, 'create'])->name('create');
        Route::post('/', [LicensePoolController::class, 'store'])->name('store');
        Route::get('/{licensePool}', [LicensePoolController::class, 'show'])->name('show');
        Route::put('/{licensePool}', [LicensePoolController::class, 'update'])->name('update');
        Route::delete('/{licensePool}', [LicensePoolController::class, 'destroy'])->name('destroy');
        
        Route::post('/{id}/revalidate', [LicensePoolController::class, 'revalidate'])->name('revalidate');
        Route::post('/bulk-revalidate', [LicensePoolController::class, 'bulkRevalidate'])->name('bulk-revalidate');
        Route::get('/invalid-keys', [LicensePoolController::class, 'showInvalidKeys'])->name('invalid-keys');
        Route::get('/export', [LicensePoolController::class, 'export'])->name('export');
    });
    
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::put('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{user}/licenses', [UserController::class, 'userLicenses'])->name('licenses');
        Route::get('/{user}/orders', [UserController::class, 'userOrders'])->name('orders');
    });
    
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::put('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/resend-license', [OrderController::class, 'resendLicense'])->name('resend-license');
    });
    
    Route::prefix('warranty')->name('warranty.')->group(function () {
        Route::get('/', [WarrantyController::class, 'index'])->name('index');
        Route::get('/pending', [WarrantyController::class, 'pending'])->name('pending');
        Route::put('/{warrantyExchange}/approve', [WarrantyController::class, 'approve'])->name('approve');
        Route::put('/{warrantyExchange}/reject', [WarrantyController::class, 'reject'])->name('reject');
        Route::get('/{warrantyExchange}', [WarrantyController::class, 'show'])->name('show');
    });
    
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('system')->name('system.')->group(function () {
        Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [SystemController::class, 'optimize'])->name('optimize');
        Route::post('/maintenance/enable', [SystemController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [SystemController::class, 'disableMaintenance'])->name('maintenance.disable');
    });
    
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/data', [ReportController::class, 'salesData'])->name('sales.data');
        Route::get('/activations', [ReportController::class, 'activations'])->name('activations');
        Route::get('/activations/data', [ReportController::class, 'activationsData'])->name('activations.data');
        Route::get('/license-usage', [ReportController::class, 'licenseUsage'])->name('license-usage');
        Route::get('/license-usage/data', [ReportController::class, 'licenseUsageData'])->name('license-usage.data');
        Route::get('/warranty-claims', [ReportController::class, 'warrantyClaims'])->name('warranty-claims');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
});