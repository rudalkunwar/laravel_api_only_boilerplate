<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Verify the user's email address from a signed URL.
     */
    public function verify(int $id, string $hash): JsonResponse
    {
        $user = $this->users->findById($id);

        if (!$user instanceof User || !hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            throw new HttpException(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::message('Email address already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return ApiResponse::message('Email address verified successfully.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new HttpException(401, 'Authentication required.');
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::message('Email address already verified.');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::message('Verification link sent.');
    }
}
