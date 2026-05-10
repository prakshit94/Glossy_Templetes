<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Models\Activity;

class LogAuthenticationActivity
{
    /**
     * Handle authentication events.
     */
    public function handle(object $event): void
    {
        $description = '';
        $properties = [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($event instanceof Login) {
            $description = 'User logged in';
            $user = $event->user;
            activity('auth')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties($properties)
                ->log($description);
        } elseif ($event instanceof Logout) {
            $description = 'User logged out';
            $user = $event->user;
            if ($user) {
                activity('auth')
                    ->performedOn($user)
                    ->causedBy($user)
                    ->withProperties($properties)
                    ->log($description);
            }
        } elseif ($event instanceof Failed) {
            $description = 'Failed login attempt';
            $properties['credentials'] = [
                'email' => $event->credentials['email'] ?? ($event->credentials['username'] ?? 'unknown'),
            ];
            activity('auth')
                ->withProperties($properties)
                ->log($description);
        }
    }
}
