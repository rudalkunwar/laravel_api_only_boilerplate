<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Data\AuthTokenData;
use App\Auth\Data\LoginData;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(LoginData $data): AuthTokenData
    {
        $user = $this->users->findByEmail($data->email);

        if ($user === null || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($data->deviceName)->plainTextToken;

        return new AuthTokenData(user: $user, token: $token);
    }
}
