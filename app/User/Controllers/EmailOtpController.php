<?php

declare(strict_types=1);

namespace App\User\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use App\User\Actions\SendEmailOtpAction;
use App\User\Actions\VerifyEmailOtpAction;
use App\User\Models\User;
use App\User\Requests\SendEmailOtpRequest;
use App\User\Requests\VerifyEmailOtpRequest;
use App\User\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class EmailOtpController extends Controller
{
    public function __construct(
        private readonly SendEmailOtpAction $sendEmailOtp,
        private readonly VerifyEmailOtpAction $verifyEmailOtp,
    ) {}

    public function sendOtp(SendEmailOtpRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->sendEmailOtp->execute($user, $request->string('email')->toString());

        return ApiResponse::message('OTP sent to your email.');
    }

    public function verifyOtp(VerifyEmailOtpRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user = $this->verifyEmailOtp->execute(
            $user,
            $request->string('email')->toString(),
            $request->string('otp')->toString(),
        );

        return ApiResponse::success(
            UserResource::make($user),
            'Email verified successfully.',
        );
    }
}
