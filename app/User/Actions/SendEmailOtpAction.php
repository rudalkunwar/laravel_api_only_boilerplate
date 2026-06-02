<?php

declare(strict_types=1);

namespace App\User\Actions;

use App\User\Models\EmailOtp;
use App\User\Models\User;
use Illuminate\Support\Carbon;

final readonly class SendEmailOtpAction
{
    public function execute(User $user, string $email): EmailOtp
    {
        $user->emailOtps()->whereNull('verified_at')->where('expires_at', '>', now())->delete();

        return $user->emailOtps()->create([
            'email' => $email,
            'otp' => (string) random_int(100000, 999999),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
    }
}
