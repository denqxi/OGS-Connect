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
            'prevent.back' => \App\Http\Middleware\PreventBackButton::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'input.validation' => \App\Http\Middleware\InputValidation::class,
        ]);
        
        // Add security headers to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        // Rate limiting can be configured here if needed
        // $middleware->throttleApi();
        
        // Ensure CSRF protection is enabled for web routes
        $middleware->validateCsrfTokens(except: [
            'api/get-security-question'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
