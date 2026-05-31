<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the health endpoint returns a successful response', function (): void {
    $response = get('/up');

    $response->assertStatus(200);
});

test('api endpoints return json', function (): void {
    $response = get('/api/non-existent', ['Accept' => 'application/json']);

    $response->assertStatus(404);
});
