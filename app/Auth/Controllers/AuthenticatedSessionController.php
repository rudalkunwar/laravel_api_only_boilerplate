<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Actions\AuthenticateUserAction;
use App\Auth\Actions\LogoutUserAction;
use App\Auth\Data\LoginData;
use App\Auth\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use App\User\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserAction $authenticateUser,
        private readonly LogoutUserAction $logoutUser,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function store(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticateUser->execute(LoginData::fromArray($request->validated()));

        $user = $this->users->findById($result->userId);

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($user)->resolve(),
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
