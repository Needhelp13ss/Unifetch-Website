<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackActivity::class);

        $middleware->alias([
            'shop' => \App\Http\Middleware\EnsureShop::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'runner' => \App\Http\Middleware\EnsureRunner::class,
            'shopper' => \App\Http\Middleware\EnsureNotAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
