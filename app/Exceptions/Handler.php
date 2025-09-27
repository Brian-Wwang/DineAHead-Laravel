<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // 1. 参数校验异常
        if ($e instanceof ValidationException) {
            return api_response(
                $e->errors(),
                $e->getMessage(),
                422,
                false
            );
        }

        // 2. HTTP 异常（404、403、401 等）
        if ($e instanceof HttpException) {
            return api_response(
                null,
                $e->getMessage() ?: 'HTTP Error',
                $e->getStatusCode(),
                false
            );
        }

        // 3. 业务逻辑自定义异常（见下方）
        if ($e instanceof BusinessException) {
            return api_response(
                $e->getData(),
                $e->getMessage(),
                $e->getCode() ?: 400,
                false
            );
        }

        // 4. 其他未捕获异常（500）
        return api_response(
            config('app.debug') ? ['trace' => $e->getTrace()] : null,
            $e->getMessage(),
            500,
            false
        );
    }
}
