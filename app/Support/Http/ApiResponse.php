<?php

declare(strict_types=1);

namespace App\Support\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

/**
 * Builds a consistent JSON envelope for all API responses.
 *
 * Successful payloads are wrapped as `{"data": ..., "message": ...}` while
 * paginated payloads additionally expose `meta` and `links`.
 */
final class ApiResponse
{
    /**
     * @param  array<string, string>  $headers
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        $payload = ['data' => self::normalize($data)];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return new JsonResponse($payload, $status, $headers);
    }

    public static function message(string $message, int $status = 200): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }

    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse($payload, $status);
    }

    private static function normalize(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        if ($data instanceof AbstractPaginator || $data instanceof ResourceCollection) {
            return $data;
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof Responsable) {
            return $data;
        }

        return $data;
    }
}
