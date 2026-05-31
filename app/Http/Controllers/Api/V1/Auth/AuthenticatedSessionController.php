<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\AuthenticateUserAction;
use App\Domain\Auth\Actions\LogoutUserAction;
use App\Domain\Auth\Data\LoginData;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserAction $authenticateUser,
        private readonly LogoutUserAction $logoutUser,
    ) {}

    public function store(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticateUser->execute(LoginData::fromArray($request->validated()));

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result->user)->resolve(),
                'token' => $result->token,
                'token_type' => $result->tokenType,
            ],
            message: 'Authenticated successfully.',
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->logoutUser->execute($user);

        return ApiResponse::message('Logged out successfully.');
    }
}
