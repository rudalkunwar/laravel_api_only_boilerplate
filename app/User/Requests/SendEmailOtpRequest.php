<?php

declare(strict_types=1);

namespace App\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SendEmailOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'lowercase',
                Rule::unique('users', 'email')->ignore($this->user()),
            ],
        ];
    }
}
