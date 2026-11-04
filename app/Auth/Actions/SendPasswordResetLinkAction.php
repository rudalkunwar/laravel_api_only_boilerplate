<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Illuminate\Auth\Passwords\PasswordBroker;

final readonly class SendPasswordResetLinkAction
{
    public function __construct(
        private PasswordBroker $broker,
    ) {}

    public function execute(string $email): string
    {
        $this->broker->sendResetLink(['email' => $email]);

        return PasswordBroker::RESET_LINK_SENT;
    }
}
