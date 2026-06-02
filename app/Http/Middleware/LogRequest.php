<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LogRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($request->user() === null) {
            return;
        }

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'status' => $response->getStatusCode(),
            ])
            ->event('request')
            ->log('{causer.name} made a {properties.method} request to {properties.url}');
    }
}
