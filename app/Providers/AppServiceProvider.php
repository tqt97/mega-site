<?php

namespace App\Providers;

use App\Models\Setting;
use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);
        Model::preventLazyLoading(! app()->isProduction());
        Setting::observe(SettingObserver::class);
    }
}
