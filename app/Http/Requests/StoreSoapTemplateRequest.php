<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoapTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ($this->user()->role === 'doctor' || $this->user()->doctor !== null);
    }

    public function rules(): array
    {
        return [
            'template_name' => ['required', 'string', 'max:100'],
            'subjective_template' => ['nullable', 'string', 'max:2000'],
            'objective_template' => ['nullable'],
            'assessment_template' => ['nullable', 'string', 'max:2000'],
            'plan_template' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
