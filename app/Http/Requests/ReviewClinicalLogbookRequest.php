<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewClinicalLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya dokter yang dapat memberikan supervisi / review logbook
        $user = $this->user();
        return $user && $user->doctor !== null;
    }

    public function rules(): array
    {
        return [
            'status'              => ['required', 'string', 'in:approved,revision_needed'],
            'supervisor_feedback' => ['required', 'string'],
            'score'               => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'              => 'Keputusan supervisi (Disetujui / Perlu Revisi) wajib dipilih.',
            'supervisor_feedback.required' => 'Umpan balik dan catatan evaluasi DPJP wajib diisi.',
            'score.min'                    => 'Nilai minimal adalah 0.',
            'score.max'                    => 'Nilai maksimal adalah 100.',
        ];
    }
}
