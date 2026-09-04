<?php

namespace App\Providers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production') || request()->header('x-forwarded-proto') === 'https' || str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        try {
            $tz = CompanySetting::get('timezone', config('app.timezone', 'Asia/Kolkata'));
            if ($tz) {
                date_default_timezone_set($tz);
                config(['app.timezone' => $tz]);
            }
        } catch (\Throwable $e) {
            // Gracefully handle if database or settings table not yet available
        }
    }
}
