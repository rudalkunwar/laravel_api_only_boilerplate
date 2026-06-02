<?php

declare(strict_types=1);

namespace App\Subscription\Requests;

use App\Subscription\Enums\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::in([Plan::Monthly->value, Plan::Yearly->value])],
            'coupon' => ['nullable', 'string'],
            'allow_promotion_codes' => ['nullable', 'boolean'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ];
    }
}
