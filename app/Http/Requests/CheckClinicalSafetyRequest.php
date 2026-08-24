<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckClinicalSafetyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['nullable', 'integer', 'exists:patient,patient_id'],
            'medicines' => ['required', 'array'],
            'medicines.*' => ['nullable'],
        ];
    }
}
