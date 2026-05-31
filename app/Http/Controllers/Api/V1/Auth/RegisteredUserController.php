<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\Data\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

final class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUser,
    ) {}

    public function store(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUser->execute(RegisterData::fromArray($request->validated()));

        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($user)->resolve(),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            message: 'Registration successful.',
            status: 201,
        );
    }
}
