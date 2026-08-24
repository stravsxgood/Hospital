<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashierShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $nurse = $this->user()?->nurse;
        return $nurse !== null && $nurse->isTetap();
    }

    public function rules(): array
    {
        return [
            'shift_name'   => ['required', 'string', 'in:Pagi,Siang,Malam'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
