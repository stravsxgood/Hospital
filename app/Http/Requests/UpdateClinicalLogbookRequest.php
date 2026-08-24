<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicalLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->nurse !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'required', 'integer', 'exists:patient,patient_id'],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctor,doctor_id'],
            'medical_record_id' => ['nullable', 'integer', 'exists:medical_record,medical_record_id'],
            'activity_type' => ['sometimes', 'required', 'string', 'in:anamnesis,physical_exam,procedure_assistance,case_discussion'],
            'case_title' => ['sometimes', 'required', 'string', 'max:150'],
            'clinical_findings' => ['sometimes', 'required', 'string'],
            'procedure_performed' => ['nullable', 'string'],
            'learning_reflection' => ['sometimes', 'required', 'string'],
            'submit_now' => ['nullable', 'boolean'],
        ];
    }
}
