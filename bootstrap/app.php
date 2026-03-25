<?php

use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);
        $middleware->append(ContentSecurityPolicy::class);
        $middleware->encryptCookies(except: [
            Str::slug(env('APP_NAME', 'laravel'), '_').'_cookie_consent',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
