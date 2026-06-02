<?php

declare(strict_types=1);

use App\Admin\Controllers\PermissionController;
use App\Admin\Controllers\RoleController;
use App\Admin\Controllers\UserController as AdminUserController;
use App\Auth\Controllers\AuthenticatedSessionController;
use App\Auth\Controllers\EmailVerificationController;
use App\Auth\Controllers\NewPasswordController;
use App\Auth\Controllers\PasswordResetLinkController;
use App\Auth\Controllers\RegisteredUserController;
use App\Health\HealthController;
use App\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Health
    Route::get('health', HealthController::class)->name('health');

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

    // Authenticated user
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::middleware('throttle:api')->group(function (): void {
            Route::get('user', [UserController::class, 'show'])->name('user.show');
            Route::put('user', [UserController::class, 'update'])->name('user.update');
        });
    });

    // Admin routes (auth + admin role required)
    Route::middleware(['auth:sanctum', 'role:admin', 'throttle:api'])->prefix('admin')->name('admin.')->group(function (): void {
        // User management
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Role & permission management
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });
});
