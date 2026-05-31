<?php

declare(strict_types=1);

use App\Support\Http\ApiResponse;
use Illuminate\Http\Resources\Json\JsonResource;

it('wraps data in a data envelope', function (): void {
    $response = ApiResponse::success(['id' => 1]);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toBe(['data' => ['id' => 1]]);
});

it('includes a message and custom status when provided', function (): void {
    $response = ApiResponse::success(['id' => 1], 'Created.', 201);

    expect($response->getStatusCode())->toBe(201)
        ->and($response->getData(true))->toBe([
            'data' => ['id' => 1],
            'message' => 'Created.',
        ]);
});

it('resolves a json resource into the data envelope', function (): void {
    $response = ApiResponse::success(JsonResource::make(['name' => 'Ada']));

    expect($response->getData(true))->toBe(['data' => ['name' => 'Ada']]);
});

it('builds a bare message response', function (): void {
    $response = ApiResponse::message('Done.', 202);

    expect($response->getStatusCode())->toBe(202)
        ->and($response->getData(true))->toBe(['message' => 'Done.']);
});

it('builds an error response with validation errors', function (): void {
    $response = ApiResponse::error('Invalid.', 422, ['email' => ['Required.']]);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getData(true))->toBe([
            'message' => 'Invalid.',
            'errors' => ['email' => ['Required.']],
        ]);
});

it('omits the errors key when there are none', function (): void {
    $response = ApiResponse::error('Bad request.');

    expect($response->getData(true))->toBe(['message' => 'Bad request.']);
});
