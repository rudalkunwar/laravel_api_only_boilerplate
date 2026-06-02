<?php

declare(strict_types=1);

namespace App\User\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Actions\UpdateUserProfileAction;
use App\User\Data\UpdateProfileData;
use App\User\Models\User;
use App\User\Requests\UpdateProfileRequest;
use App\User\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    public function __construct(
        private readonly UpdateUserProfileAction $updateUserProfile,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(UserResource::make($user));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updated = $this->updateUserProfile->execute(
            $user,
            UpdateProfileData::fromArray($request->validated()),
        );

        return ApiResponse::success(UserResource::make($updated), 'Profile updated.');
    }
}
