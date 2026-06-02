<?php

declare(strict_types=1);

namespace App\OAuth\Controllers;

use App\Http\Controllers\Controller;
use App\OAuth\Actions\AuthenticateSocialUserAction;
use App\OAuth\Data\SocialUserData;
use App\Support\Http\ApiResponse;
use App\User\Resources\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class SocialiteController extends Controller
{
    private const array SUPPORTED_PROVIDERS = ['google', 'apple'];

    public function __construct(
        private readonly AuthenticateSocialUserAction $authenticateSocialUser,
    ) {}

    public function redirect(string $provider): JsonResponse
    {
        $this->validateProvider($provider);

        $redirectUrl = $this->driver($provider)->stateless()->redirect()->getTargetUrl();

        return ApiResponse::success(data: ['redirect_url' => $redirectUrl]);
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        $this->validateProvider($provider);

        try {
            $socialiteUser = $this->driver($provider)->stateless()->user();
        } catch (Exception) {
            throw new UnprocessableEntityHttpException('Unable to authenticate with '.$provider.'.');
        }

        $data = SocialUserData::fromArray([
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail() ?? '',
            'avatar_url' => $socialiteUser->getAvatar(),
        ]);

        $result = $this->authenticateSocialUser->execute($data);

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result->user)->resolve(),
                'token' => $result->token,
                'token_type' => $result->tokenType,
            ],
            message: 'Authenticated successfully.',
        );
    }

    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            throw new UnprocessableEntityHttpException('Provider ['.$provider.'] is not supported.');
        }
    }

    /**
     * Resolve a stateless-capable OAuth provider driver.
     */
    private function driver(string $provider): AbstractProvider
    {
        $driver = Socialite::driver($provider);

        if (!$driver instanceof AbstractProvider) {
            throw new UnprocessableEntityHttpException('Provider ['.$provider.'] does not support stateless authentication.');
        }

        return $driver;
    }
}
