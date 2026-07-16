<?php

namespace App\Providers;

use App\Models\UISetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

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
        Gate::before(function ($user, $ability) {
            if(config('app.debug') && $user->hasRole('Super Admin')) {
                return true;
            }
        });
        DB::statement('SET time_zone = "+08:00"');
        $settings = new UISetting();

        if (Schema::hasTable('ui_settings')) {
            $settings = UISetting::first() ?? $settings;
        }

        View::share('settings', $settings);
    }
}
