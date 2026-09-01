<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Validates profile update requests for authenticated users in the Hospital Population system.
 *
 * Ensures user name is provided, email is valid and unique (ignoring current user ID),
 * and an optional phone number complies with standard length constraints.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|Unique|\Illuminate\Contracts\Validation\Rule|array<mixed>|string>|string>
     */
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->user();
        $userId = $user?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $userId !== null
                    ? Rule::unique(User::class, 'email')->ignore($userId)
                    : Rule::unique(User::class, 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
