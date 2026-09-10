<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * Mencatat setiap login pengurus ke Log Aktivitas (PRD 5.2.16).
 */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        activity('user')
            ->performedOn($user)
            ->causedBy($user)
            ->event('login')
            ->log('Masuk ke admin panel');
    }
}
