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
        // This callback REPLACES Laravel's default check rather than adding
        // to it. The original version only tested `is('api/*')` — and this
        // app has no api/* routes — so every fetch() the customer makes got
        // an HTML body or a 302 redirect instead of JSON: validation errors
        // never reached them, and 403/419/429 were unreadable. Keeping
        // expectsJson() here is what allows the toast system to say anything
        // at all. Do not drop it when adding future api routes.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
