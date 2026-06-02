<?php

declare(strict_types=1);

it('lists all subscription plans', function (): void {
    $response = $this->getJson('/api/v1/subscriptions/plans');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'interval', 'features'],
            ],
        ]);
});
