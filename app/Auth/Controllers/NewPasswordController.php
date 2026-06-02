<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Actions\ResetUserPasswordAction;
use App\Auth\Data\ResetPasswordData;
use App\Auth\Requests\ResetPasswordRequest;
use App\Http\Controllers\Controller;
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
