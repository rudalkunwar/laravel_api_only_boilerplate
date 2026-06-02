<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Data\AuthTokenData;
use App\Auth\Data\LoginData;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Hashing\HashManager;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private HashManager $hash,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(LoginData $data): AuthTokenData
    {
        $user = $this->users->findByEmail($data->email);

        if (!$user instanceof User || !$this->hash->check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($data->deviceName)->plainTextToken;

        return new AuthTokenData(
            userId: $user->id,
            userName: $user->name,
            userEmail: (string) $user->email,
            token: $token,
        );
    }
}
