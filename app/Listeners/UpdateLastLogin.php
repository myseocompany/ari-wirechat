<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user) {
            return;
        }

        $event->user->forceFill([
            'last_login' => now(),
        ])->save();
    }
}
