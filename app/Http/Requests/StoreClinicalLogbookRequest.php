<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicalLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya user dengan profil perawat/koas yang dapat membuat logbook klinis
        $user = $this->user();
        return $user && $user->nurse !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id'          => ['required', 'integer', 'exists:patient,patient_id'],
            'doctor_id'           => ['required', 'integer', 'exists:doctor,doctor_id'],
            'medical_record_id'   => ['nullable', 'integer', 'exists:medical_record,medical_record_id'],
            'activity_type'       => ['required', 'string', 'in:anamnesis,physical_exam,procedure_assistance,case_discussion'],
            'case_title'          => ['required', 'string', 'max:150'],
            'clinical_findings'   => ['required', 'string'],
            'procedure_performed' => ['nullable', 'string'],
            'learning_reflection' => ['required', 'string'],
            'submit_now'          => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required'          => 'Pasien wajib dipilih.',
            'doctor_id.required'           => 'Dokter DPJP Pembimbing wajib dipilih.',
            'activity_type.required'       => 'Jenis aktivitas klinis wajib ditentukan.',
            'case_title.required'          => 'Judul kasus klinis wajib diisi.',
            'clinical_findings.required'   => 'Temuan klinis kasus wajib diuraikan.',
            'learning_reflection.required' => 'Refleksi pembelajaran wajib diisi.',
        ];
    }
}
