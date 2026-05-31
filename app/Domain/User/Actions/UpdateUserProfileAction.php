<?php

declare(strict_types=1);

namespace App\Domain\User\Actions;

use App\Domain\User\Data\UpdateProfileData;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

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
