<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            '2fa' => \PragmaRX\Google2FALaravel\Middleware\Authenticator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException || 
                $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'This action is unauthorized.'], 403);
                }

                $previousUrl = url()->previous();
                $currentUrl = url()->current();

                // Redirect to dashboard if there is no previous URL or it's the same as current
                if ($previousUrl === $currentUrl || $previousUrl === url('/')) {
                    return redirect()->route('dashboard')->with('error', 'You do not have the required permissions to perform this action.');
                }

                return back()->with('error', 'You do not have the required permissions to perform this action.');
            }
            
            return null; // Let Laravel handle other exceptions
        });
    })->create();
