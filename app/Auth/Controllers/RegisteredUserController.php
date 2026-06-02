<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Actions\RegisterUserAction;
use App\Auth\Data\RegisterData;
use App\Auth\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Resources\UserResource;
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
