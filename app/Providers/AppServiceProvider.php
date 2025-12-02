<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\Payment\TripayService::class);
        $this->app->singleton(\App\Services\License\PidKeyService::class);
        $this->app->singleton(\App\Services\License\CidService::class);
        $this->app->singleton(\App\Services\License\LicenseAssigner::class);
        $this->app->singleton(\App\Services\License\LicenseValidator::class);
        $this->app->singleton(\App\Services\License\WarrantyProcessor::class);
        $this->app->singleton(\App\Services\Payment\PaymentProcessor::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        \App::setLocale('id');
        
        Validator::extend('installation_id_format', function ($attribute, $value, $parameters, $validator) {
            $digitsOnly = preg_replace('/[^0-9]/', '', $value);
            return in_array(strlen($digitsOnly), [54, 63]);
        }, 'Installation ID harus 54 atau 63 digit angka.');
        
        Validator::extend('license_key_format', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/', $value) === 1;
        }, 'Format lisensi tidak valid. Contoh: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX');
        
        Validator::extend('no_dashes', function ($attribute, $value, $parameters, $validator) {
            return strpos($value, '-') === false;
        }, 'Tidak boleh mengandung tanda dash (-)');
        
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentUser', auth()->user());
                
                $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
                
                $view->with('unreadNotificationsCount', $unreadNotifications);
            }
            
            $cartCount = count(session('cart', []));
            $view->with('cartCount', $cartCount);
        });
        
        date_default_timezone_set('Asia/Jakarta');
    }
}