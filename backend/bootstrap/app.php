<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\AuthenticatePlayer;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'player.auth' => AuthenticatePlayer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ApiException) {
                return ApiResponse::error(
                    $exception->getMessage(),
                    $exception->getApiCode(),
                    $exception->getHttpStatus(),
                    $exception->getData(),
                );
            }

            if ($exception instanceof ValidationException) {
                return ApiResponse::error(
                    '参数错误',
                    42200,
                    SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                    [
                        'errors' => $exception->errors(),
                    ],
                );
            }

            if ($exception instanceof AuthenticationException) {
                return ApiResponse::error('未登录', 40101, SymfonyResponse::HTTP_UNAUTHORIZED);
            }

            return ApiResponse::error(
                app()->hasDebugModeEnabled() ? $exception->getMessage() : '服务器繁忙，请稍后再试',
                50000,
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
            );
        });
    })->create();
