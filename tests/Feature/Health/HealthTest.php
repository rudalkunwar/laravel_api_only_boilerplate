<?php

declare(strict_types=1);

test('health endpoint returns success for unauthenticated request', function (): void {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['checks' => ['database' => ['healthy', 'message'], 'cache' => ['healthy', 'message']]],
            'message',
        ]);
});
