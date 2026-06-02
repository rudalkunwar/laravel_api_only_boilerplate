<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Data\ResetPasswordData;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Hashing\HashManager;
use Illuminate\Validation\ValidationException;

final readonly class ResetUserPasswordAction
{
    public function __construct(
        private PasswordBroker $broker,
        private UserRepositoryInterface $users,
        private HashManager $hash,
        private Dispatcher $events,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(ResetPasswordData $data): string
    {
        $status = $this->broker->reset(
            [
                'email' => $data->email,
                'password' => $data->password,
                'password_confirmation' => $data->password,
                'token' => $data->token,
            ],
            function (CanResetPassword $user, string $password): void {
                /** @var \App\User\Models\User $user */
                $this->users->resetPassword($user, $this->hash->make($password));

                $this->events->dispatch(new PasswordReset($user));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }
}
