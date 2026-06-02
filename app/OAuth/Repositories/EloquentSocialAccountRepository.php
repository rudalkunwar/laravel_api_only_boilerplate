<?php

declare(strict_types=1);

namespace App\OAuth\Repositories;

use App\OAuth\Models\SocialAccount;

final class EloquentSocialAccountRepository implements SocialAccountRepositoryInterface
{
    public function findByProvider(string $provider, string $providerId): ?SocialAccount
    {
        return SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SocialAccount
    {
        return SocialAccount::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SocialAccount $socialAccount, array $data): SocialAccount
    {
        $socialAccount->update($data);

        return $socialAccount->refresh();
    }
}
