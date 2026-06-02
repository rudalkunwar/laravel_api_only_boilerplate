<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Repositories\PermissionRepositoryInterface;
use App\Admin\Resources\PermissionResource;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(PermissionResource::collection($this->permissions->all()));
    }
}
