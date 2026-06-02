<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Data\RegisterData;
use App\Auth\Enums\Role;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
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

            $this->users->assignRole($user, Role::User->value);

            event(new Registered($user));

            return $user;
        });
    }
}
