<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('app_settings')) {
                $timezone = AppSetting::get('app_timezone', config('app.timezone', 'Asia/Jakarta'));
                if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
                    date_default_timezone_set($timezone);
                    config(['app.timezone' => $timezone]);
                }
            }
        } catch (\Throwable) {
            // Ignore database unreachable during early boot/migration
        }
    }
}
