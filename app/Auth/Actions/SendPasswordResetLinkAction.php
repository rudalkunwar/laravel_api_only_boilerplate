<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Validation\ValidationException;

final readonly class SendPasswordResetLinkAction
{
    public function __construct(
        private PasswordBroker $broker,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(string $email): string
    {
        $status = $this->broker->sendResetLink(['email' => $email]);

        if ($status !== PasswordBroker::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }
}
