<?php

declare(strict_types=1);

use App\Auth\Controllers\AuthenticatedSessionController;
use App\Auth\Controllers\EmailVerificationController;
use App\Subscription\Controllers\SubscriptionController;
use App\User\Controllers\EmailOtpController;
use App\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::middleware('throttle:api')->group(function (): void {
        Route::get('user', [UserController::class, 'show'])->name('user.show');
        Route::put('user', [UserController::class, 'update'])->name('user.update');

        // Email OTP verification
        Route::post('user/email/send-otp', [EmailOtpController::class, 'sendOtp'])->name('user.email.send-otp');
        Route::post('user/email/verify-otp', [EmailOtpController::class, 'verifyOtp'])->name('user.email.verify-otp');

        Route::get('subscriptions/current', [SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('subscriptions/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
        Route::post('subscriptions/portal', [SubscriptionController::class, 'portal'])->name('subscriptions.portal');
        Route::post('subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    });
});
