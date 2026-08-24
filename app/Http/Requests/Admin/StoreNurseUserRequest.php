<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNurseUserRequest extends FormRequest
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
            // User Account
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'            => ['nullable', 'string', 'min:8'],

            // Nurse / Intern Profile
            'type'                => ['required', 'string', 'in:tetap,koas'],
            'registration_number' => ['nullable', 'string', 'max:50', 'unique:nurse,registration_number'],
            'gender'              => ['required', 'string', 'in:Laki-laki,Perempuan'],
            'institute'           => ['required_if:type,koas', 'nullable', 'string', 'max:255'],
            'date_start'          => ['nullable', 'date'],
            'date_end'            => ['nullable', 'date', 'after_or_equal:date_start'],
        ];
    }
}
