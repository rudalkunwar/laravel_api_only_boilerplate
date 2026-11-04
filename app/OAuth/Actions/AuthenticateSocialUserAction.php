<?php

declare(strict_types=1);

namespace App\OAuth\Actions;

use App\Auth\Enums\Role;
use App\OAuth\Data\SocialAuthResult;
use App\OAuth\Data\SocialUserData;
use App\OAuth\Models\SocialAccount;
use App\OAuth\Repositories\SocialAccountRepositoryInterface;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final readonly class AuthenticateSocialUserAction
{
    public function __construct(
        private SocialAccountRepositoryInterface $socialAccounts,
        private UserRepositoryInterface $users,
    ) {}

    public function execute(SocialUserData $data): SocialAuthResult
    {
        $socialAccount = $this->socialAccounts->findByProvider($data->provider, $data->providerId);

        if ($socialAccount instanceof SocialAccount) {
            $user = $socialAccount->user;

            if ($data->avatarUrl !== null && $data->avatarUrl !== $socialAccount->avatar_url) {
                $this->socialAccounts->update($socialAccount, ['avatar_url' => $data->avatarUrl]);
            }
        } else {
            $user = $data->email !== null ? $this->users->findByEmail($data->email) : null;

            throw_if(
                $user instanceof User,
                UnprocessableEntityHttpException::class,
                'An account with this email already exists. Sign in to link a social account.',
            );

            $user = $this->users->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Str::password(32),
            ]);

            $this->users->assignRole($user, Role::User->value);

            $this->socialAccounts->create([
                'user_id' => $user->id,
                'provider' => $data->provider,
                'provider_id' => $data->providerId,
                'avatar_url' => $data->avatarUrl,
            ]);
        }

        $token = $this->users->createToken($user, $data->provider);

        return new SocialAuthResult(
            user: $user,
            token: $token,
        );
    }
}
