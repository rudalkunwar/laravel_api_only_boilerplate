<?php

declare(strict_types=1);

namespace App\User\Actions;

use App\User\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class VerifyEmailOtpAction
{
    public function execute(User $user, string $email, string $otp): User
    {
        $emailOtp = $user->emailOtps()
            ->where('email', $email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->first();

        if ($emailOtp === null || !$emailOtp->isValid()) {
            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is invalid or has expired.'],
            ]);
        }

        $emailOtp->update(['verified_at' => now()]);

        $user->update([
            'email' => $email,
            'email_verified_at' => now(),
        ]);

        return $user->fresh();
    }
}
