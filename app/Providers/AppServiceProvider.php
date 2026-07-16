<?php

namespace App\Providers;

use App\Listeners\UpdateLastLogin;
use App\Listeners\WAToolboxListener;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Carbon::setLocale(App::getLocale());
        Passport::authorizationView('mcp.authorize');

        Event::listen(
            \Namu\WireChat\Events\MessageCreated::class,
            WAToolboxListener::class,
        );

        Event::listen(Login::class, UpdateLastLogin::class);
        Paginator::useBootstrap();
    }
}
