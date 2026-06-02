<?php

declare(strict_types=1);

namespace App\User\Actions;

use App\User\Models\EmailOtp;
use App\User\Models\User;
use App\User\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

final readonly class SendEmailOtpAction
{
    public function execute(User $user, string $email): EmailOtp
    {
        $user->emailOtps()->whereNull('verified_at')->where('expires_at', '>', now())->delete();

        $otp = (string) random_int(100000, 999999);

        $emailOtp = $user->emailOtps()->create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Date::now()->addMinutes(10),
        ]);

        Notification::route('mail', $email)->notify(new EmailOtpNotification($otp));

        return $emailOtp;
    }
}
