<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', [
            \App\Http\Middleware\RequestIdMiddleware::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'advertiser.approved' => \App\Http\Middleware\EnsureAdvertiserApproved::class,
        ]);

        // ❌ DO NOT put RateLimiter::for() here in Laravel 11
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\App\Exceptions\Ads\InvalidBudgetException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (\App\Exceptions\Ads\InsufficientWalletBalanceException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }); 

        $exceptions->render(function (\App\Exceptions\Ads\InvalidMediaException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (\App\Exceptions\Ads\InvalidAdvertisementStateException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (\Throwable $e, $request) {

            // Only standardize API responses
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $requestId = $request->attributes->get('request_id');

            // 1) Validation
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                    'meta' => ['request_id' => $requestId],
                ], 422);
            }

            // 2) Domain exceptions (business rules)
            if ($e instanceof \App\Exceptions\Domain\DomainException) {
                return response()->json([
                    'status' => 'error',
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                    'errors' => $e->getErrors(),
                    'meta' => ['request_id' => $requestId],
                ], $e->getHttpStatus());
            }

            // 3) Auth / Sanctum (unauthenticated)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                    'meta' => ['request_id' => $requestId],
                ], 401);
            }

            // 4) HTTP Exceptions
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();

                $code = match ($status) {
                    401 => 'UNAUTHORIZED',
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    419 => 'SESSION_EXPIRED',
                    429 => 'RATE_LIMITED',
                    default => 'HTTP_ERROR',
                };

                $message = app()->isProduction()
                    ? 'Request failed.'
                    : ($e->getMessage() ?: $code);

                return response()->json([
                    'status' => 'error',
                    'code' => $code,
                    'message' => $message,
                    'meta' => ['request_id' => $requestId],
                ], $status);
            }

            // 5) Fallback 500
            $message = app()->isProduction()
                ? 'Something went wrong. Please try again.'
                : $e->getMessage();

            return response()->json([
                'status' => 'error',
                'code' => 'INTERNAL_ERROR',
                'message' => $message,
                'meta' => ['request_id' => $requestId],
                'debug' => app()->isProduction() ? null : [
                    'type' => class_basename($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        });
    })->create();