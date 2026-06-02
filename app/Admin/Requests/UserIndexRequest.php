<?php

declare(strict_types=1);

namespace App\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'int', 'min:1', 'max:100'],
            'page' => ['sometimes', 'int', 'min:1'],
            'search' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'sort' => ['sometimes', 'string', 'in:id,name,email,created_at'],
            'direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
