<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Admin\Repositories\PermissionRepository;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

final class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionRepository $permissions,
    ) {}

    public function index(): JsonResponse
    {
        $permissions = $this->permissions->all()->map(fn (Permission $p): array => [
            'id' => $p->id,
            'name' => $p->name,
            'guard_name' => $p->guard_name,
            'created_at' => $p->created_at,
            'updated_at' => $p->updated_at,
        ]);

        return ApiResponse::success($permissions);
    }
}
