<?php

declare(strict_types=1);

namespace App\Subscription\Controllers;

use App\Http\Controllers\Controller;
use App\Subscription\Enums\Plan;
use App\Subscription\Resources\PlanResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            PlanResource::collection(Plan::cases()),
        );
    }
}
