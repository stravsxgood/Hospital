<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'search'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'role'      => ['sometimes', 'nullable', 'string', 'in:all,doctor,nurse,nurse_tetap,koas,admin,super-admin,patient'],
            'status'    => ['sometimes', 'nullable', 'string', 'in:all,active,inactive'],
            'per_page'  => ['sometimes', 'nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
