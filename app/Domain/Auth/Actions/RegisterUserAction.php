<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Data\RegisterData;
use App\Domain\Auth\Enums\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

final readonly class RegisterUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(RegisterData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = $this->users->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
            ]);

            $user->assignRole(Role::User->value);

            event(new Registered($user));

            return $user;
        });
    }
}
