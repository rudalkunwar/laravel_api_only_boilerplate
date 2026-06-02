<?php

declare(strict_types=1);

namespace App\Subscription\Resources;

use App\Subscription\Enums\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
final class PlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Plan $plan */
        $plan = $this->resource;

        return [
            'id' => $plan->value,
            'name' => $plan->label(),
            'price' => $plan->price(),
            'interval' => $plan->interval(),
            'features' => $plan->features(),
        ];
    }
}
