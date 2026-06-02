<?php

declare(strict_types=1);

namespace App\User\Actions;

use App\User\Data\UpdateProfileData;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;

final readonly class UpdateUserProfileAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, UpdateProfileData $data): User
    {
        $emailChanged = $data->email !== $user->email;

        $user = $this->users->update($user, [
            'name' => $data->name,
            'email' => $data->email,
        ]);

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
        }

        return $user;
    }
}
