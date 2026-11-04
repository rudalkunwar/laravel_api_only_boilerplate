<?php

declare(strict_types=1);

namespace App\Subscription\Requests;

use App\Subscription\Rules\FrontendUrl;
use Illuminate\Foundation\Http\FormRequest;

final class PortalRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'return_url' => ['nullable', 'url:http,https', new FrontendUrl],
        ];
    }
}
