<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // No named "login" route exists (the admin login lives at
        // /admin/login, named admin.login) — without this, Laravel's
        // default `auth` middleware would throw trying to redirect
        // unauthenticated visitors to a route that doesn't exist.
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
