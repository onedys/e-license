<?php

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\LicenseController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ActivationController;
use App\Http\Controllers\User\WarrantyController;
use App\Http\Controllers\User\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/{id}', [LicenseController::class, 'show'])->name('licenses.show');
    
    Route::middleware(['throttle:5,60'])->prefix('activation')->name('activation.')->group(function () {
        Route::get('/', [ActivationController::class, 'showForm'])->name('form');
        Route::post('/', [ActivationController::class, 'activate'])->name('process');
        Route::post('/check-installation-id', [ActivationController::class, 'checkInstallationId'])->name('checkInstallationId');
    });
    
    Route::middleware(['throttle:3,1440'])->prefix('warranty')->name('warranty.')->group(function () {
        Route::get('/claim', [WarrantyController::class, 'showClaimForm'])->name('claim.form');
        Route::post('/claim', [WarrantyController::class, 'claim'])->name('claim.process');
        Route::get('/history', [WarrantyController::class, 'history'])->name('history');
    });
    
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    });
});