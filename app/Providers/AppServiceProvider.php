<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
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
        Paginator::useBootstrap();

        App::setLocale(session('locale', config('app.locale')));

        // The admin sidebar shows a user count on every screen, not just the one
        // whose controller happens to load users. Only filled in for signed-in
        // requests, and never overrides a $widget the view was already given.
        View::composer('layouts.admin', function ($view) {
            if (! Auth::check() || isset($view->getData()['widget'])) {
                return;
            }

            $view->with('widget', ['users' => User::count()]);
        });
    }
}
