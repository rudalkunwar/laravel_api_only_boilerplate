<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\User\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class LogoutUserAction
{
    /**
     * Revoke the access token used for the current request.
     */
    public function execute(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
