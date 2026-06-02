<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\LogRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            HandleCors::class,
            ForceJsonResponse::class,
            LogRequest::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => true,
        );

        $exceptions->render(static function (AuthenticationException $e, Request $request): JsonResponse {
            return new JsonResponse(['message' => $e->getMessage()], 401);
        });

        $exceptions->render(static function (AuthorizationException $e, Request $request): JsonResponse {
            return new JsonResponse(['message' => $e->getMessage()], 403);
        });

        $exceptions->render(static function (ModelNotFoundException $e, Request $request): JsonResponse {
            return new JsonResponse(['message' => 'Resource not found.'], 404);
        });

        $exceptions->render(static function (NotFoundHttpException $e, Request $request): JsonResponse {
            return new JsonResponse(['message' => 'Resource not found.'], 404);
        });
    })->create();
