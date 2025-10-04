<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $e)
    {
        // 1. 参数校验异常
        if ($e instanceof ValidationException) {
            return api_response(
                $e->errors(),
                'Validation failed',
                422,
                false
            );
        }

        // 2. 未登录 / 权限不足
        if ($e instanceof AuthenticationException) {
            return api_response(null, 'Unauthenticated', 401, false);
        }

        if ($e instanceof AuthorizationException) {
            return api_response(null, 'Forbidden', 403, false);
        }

        // 3. HTTP 异常
        if ($e instanceof NotFoundHttpException) {
            return api_response(null, 'Resource not found', 404, false);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return api_response(null, 'Method not allowed', 405, false);
        }

        if ($e instanceof HttpException) {
            return api_response(
                null,
                $e->getMessage() ?: 'HTTP Error',
                $e->getStatusCode(),
                false
            );
        }

        // 4. 业务逻辑自定义异常
        if ($e instanceof BusinessException) {
            return api_response(
                $e->getData(),
                $e->getMessage(),
                $e->getCode() ?: 400,
                false
            );
        }

        // 5. 其他未捕获异常（500）
        $isLocal = app()->environment(['local', 'testing']);
        return api_response(
            $isLocal ? ['trace' => $e->getTrace()] : null,
            $isLocal ? $e->getMessage() : 'Server Error',
            500,
            false
        );
    }
}
