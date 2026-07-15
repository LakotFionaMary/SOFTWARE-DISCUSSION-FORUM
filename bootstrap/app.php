<?php

use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\BlacklistMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        
        health: '/up',
    )

   >withBroadcasting(
    __DIR__.'/../routes/channels.php',
     [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
    ],
)
    
    ->withMiddleware(function (Middleware $middleware) {
        // Register the custom middleware used throughout routes/api.php.
        // See SDD 5.1 ("role-based access control across all endpoints")
        // and 5.2 (blacklist enforcement).
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'blacklist' => BlacklistMiddleware::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // This line removes the security token block for the API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
