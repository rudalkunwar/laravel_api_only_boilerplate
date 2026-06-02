<?php

declare(strict_types=1);

use App\Auth\Controllers\AuthenticatedSessionController;
use App\Auth\Controllers\EmailVerificationController;
use App\Auth\Controllers\NewPasswordController;
use App\Auth\Controllers\PasswordResetLinkController;
use App\Auth\Controllers\RegisteredUserController;
use App\OAuth\Controllers\SocialiteController;
use App\Subscription\Controllers\PlanController;
use App\Subscription\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// OAuth
Route::middleware('throttle:auth')->prefix('auth')->name('auth.')->group(function (): void {
    Route::get('{provider}/redirect', [SocialiteController::class, 'redirect'])->name('provider.redirect');
    Route::get('{provider}/callback', [SocialiteController::class, 'callback'])->name('provider.callback');
});

// Public auth
Route::middleware('throttle:auth')->group(function (): void {
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Public subscription plans
Route::get('subscriptions/plans', [PlanController::class, 'index'])->name('subscriptions.plans');

// Stripe webhook
Route::post('stripe/webhook', [WebhookController::class, '__invoke'])->name('stripe.webhook');
