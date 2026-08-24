<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashierShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $nurse = $this->user()?->nurse;
        return $nurse !== null && $nurse->isTetap();
    }

    public function rules(): array
    {
        return [
            'closing_cash_actual' => ['required', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];
    }
}
