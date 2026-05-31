<?php

use function Pest\Laravel\get;

test('the health endpoint returns a successful response', function () {
    $response = get('/up');

    $response->assertStatus(200);
});

test('api endpoints return json', function () {
    $response = get('/api/non-existent', ['Accept' => 'application/json']);

    $response->assertStatus(404);
});
