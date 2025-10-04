<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckHeaderType;
use App\Exceptions\Handler;
use App\Http\Middleware\UpdateLastSeen;

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
            'update.lastseen' => UpdateLastSeen::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->renderable(function ($e, $request) {
        // 直接调用 Handler 的 render 方法
        return app(Handler::class)->render($request, $e);
      });
    })->create();
