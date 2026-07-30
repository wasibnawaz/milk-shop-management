<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->web(append: [SecurityHeaders::class]);

        // Unauthenticated users land on the login screen rather than a 500.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Signed-in users hitting a guest route go to the dashboard.
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        | Never flash credentials or reset tokens back into the session, even
        | with debug output enabled.
        */
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'token',
        ]);

        /*
        | Log client errors at warning level with enough request context to be
        | actionable. Server errors keep Laravel's default error-level report.
        | The old application had no exception handling of its own at all.
        */
        $exceptions->report(function (Throwable $e): void {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status < 500) {
                logger()->warning($e->getMessage(), [
                    'status' => $status,
                    'url' => request()->fullUrl(),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
            }
        });
    })->create();
