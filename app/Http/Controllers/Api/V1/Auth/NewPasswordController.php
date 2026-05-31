<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\ResetUserPasswordAction;
use App\Domain\Auth\Data\ResetPasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class NewPasswordController extends Controller
{
    public function __construct(
        private readonly ResetUserPasswordAction $resetUserPassword,
    ) {}

    public function store(ResetPasswordRequest $request): JsonResponse
    {
        $this->resetUserPassword->execute(ResetPasswordData::fromArray($request->validated()));

        return ApiResponse::message('Password has been reset.');
    }
}
