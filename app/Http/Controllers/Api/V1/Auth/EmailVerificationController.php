<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
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
        assert($user instanceof User);

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::message('Email address already verified.');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::message('Verification link sent.');
    }
}
