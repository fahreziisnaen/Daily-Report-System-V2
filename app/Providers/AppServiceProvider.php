<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Department;

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
        // Set Carbon locale
        Carbon::setLocale('id');

        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Setup view composers
        View::composer(['layouts.navigation', 'layouts.app'], function ($view) {
            $view->with('departments', Department::all());
        });
    }
}
