<?php

declare(strict_types=1);

namespace App\Subscription\Controllers;

use App\Http\Controllers\Controller;
use App\Subscription\Actions\CancelSubscriptionAction;
use App\Subscription\Actions\CreateCheckoutAction;
use App\Subscription\Actions\ResumeSubscriptionAction;
use App\Subscription\Data\CheckoutData;
use App\Subscription\Requests\CheckoutRequest;
use App\Subscription\Resources\SubscriptionResource;
use App\Support\Http\ApiResponse;
use App\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SubscriptionController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutAction $createCheckout,
        private readonly CancelSubscriptionAction $cancelSubscription,
        private readonly ResumeSubscriptionAction $resumeSubscription,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $subscription = $user->subscription('default');

        return ApiResponse::success(
            $subscription !== null ? SubscriptionResource::make($subscription) : null,
        );
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $checkout = $this->createCheckout->execute(
            $user,
            CheckoutData::fromArray($request->validated()),
        );

        return ApiResponse::success([
            'url' => $checkout->url,
            'id' => $checkout->id,
        ], 'Checkout session created.', 201);
    }

    public function portal(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $url = $user->billingPortalUrl(
            $request->input('return_url', config('app.url').'/dashboard'),
        );

        return ApiResponse::success(['url' => $url], 'Billing portal URL retrieved.');
    }

    public function cancel(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $subscription = $this->cancelSubscription->execute($user);

        if ($subscription === null) {
            return ApiResponse::error('No active subscription found.', 404);
        }

        return ApiResponse::success(
            SubscriptionResource::make($subscription),
            'Subscription cancelled.',
        );
    }

    public function resume(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $subscription = $this->resumeSubscription->execute($user);

        if ($subscription === null) {
            return ApiResponse::error('No subscription on grace period found.', 404);
        }

        return ApiResponse::success(
            SubscriptionResource::make($subscription),
            'Subscription resumed.',
        );
    }
}
