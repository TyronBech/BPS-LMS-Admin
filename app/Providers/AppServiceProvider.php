<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Spatie\Backup\Events\BackupWasSuccessful::class => [
            \App\Listeners\SendBackupSucceededNotification::class,
        ],
    ];
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
            return $user->hasRole('Super Admin') ? true : null;
        });
        DB::statement('SET time_zone = "+08:00"');
    }
}
