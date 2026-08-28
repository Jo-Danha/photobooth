<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\BoothSetting;

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
        View::composer('layouts.app', function ($view) {
            $setting = BoothSetting::getActiveSettings();
            $view->with([
                'boothAppName' => $setting->app_name ?: 'PHOTOBOOTH.IO',
                'boothSetting' => $setting,
                'brandPalette' => $setting->brandPalette(),
            ]);
        });
    }
}
