<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Models\Activity;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        activity('auth')
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'email' => $event->credentials['email'] ?? 'N/A',
            ])
            ->log('Failed login attempt');
    }
}
