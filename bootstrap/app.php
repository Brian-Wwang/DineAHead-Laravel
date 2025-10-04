<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckHeaderType;
use App\Exceptions\Handler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'header.type' => CheckHeaderType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ✅ 统一捕获所有异常，转发给自定义 Handler
        $exceptions->render(function (Throwable $e, Request $request) {
            return app(Handler::class)->render($request, $e);
        });
    })
    ->create();
