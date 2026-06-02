<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Actions\SendPasswordResetLinkAction;
use App\Auth\Requests\ForgotPasswordRequest;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly SendPasswordResetLinkAction $sendPasswordResetLink,
    ) {}

    public function store(ForgotPasswordRequest $request): JsonResponse
    {
        $email = (string) $request->validated('email');

        $this->sendPasswordResetLink->execute($email);

        return ApiResponse::message('Password reset link sent.');
    }
}
