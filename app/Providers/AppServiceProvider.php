<?php

namespace App\Providers;

use App\Models\IzinGuru;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        View::composer('layouts.waka.sidebar', function ($view) {
            $view->with(
                'izinMenungguNotification',
                IzinGuru::where('status', 'menunggu')->count()
            );
        });
    }
}
