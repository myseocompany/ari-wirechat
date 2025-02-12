<?php

namespace App\Providers;

use App\Listeners\WAToolboxListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;


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
        Event::listen(
            \Namu\WireChat\Events\MessageCreated::class,
            WAToolboxListener::class,
          );
        Paginator::useBootstrap();
    }
}
