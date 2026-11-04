<?php

declare(strict_types=1);

namespace App\User\Actions;

use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class VerifyEmailOtpAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, string $email, string $otp): User
    {
        return DB::transaction(function () use ($user, $email, $otp): User {
            $emailOtp = $user->emailOtps()
                ->where('email', $email)
                ->whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($emailOtp === null || !Hash::check($otp, $emailOtp->otp_hash)) {
                throw ValidationException::withMessages([
                    'otp' => ['The provided OTP is invalid or has expired.'],
                ]);
            }

            $emailOtp->update(['verified_at' => now()]);

            return $this->users->update($user, [
                'email' => $email,
                'email_verified_at' => now(),
            ]);
        });
    }
}
