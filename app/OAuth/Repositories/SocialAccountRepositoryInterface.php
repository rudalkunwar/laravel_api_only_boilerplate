<?php

declare(strict_types=1);

namespace App\OAuth\Repositories;

use App\OAuth\Models\SocialAccount;

interface SocialAccountRepositoryInterface
{
    public function findByProvider(string $provider, string $providerId): ?SocialAccount;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SocialAccount;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SocialAccount $socialAccount, array $data): SocialAccount;
}
