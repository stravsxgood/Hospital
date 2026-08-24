<?php

namespace App\Http\Requests;

use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya pasien yang login dan memiliki data profil patient yang diizinkan
        return $this->user() && $this->user()->patient !== null;
    }

    public function rules(): array
    {
        return [
            'doctor_schedule_id' => ['required', 'exists:doctor_schedule,doctor_schedule_id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'complaint' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $schedule = DoctorSchedule::find($this->doctor_schedule_id);

            if (! $schedule) {
                return;
            }

            // Validasi: Hari kunjungan harus sesuai dengan hari praktik dokter
            $date = Carbon::parse($this->appointment_date);
            $dayName = $date->locale('id')->isoFormat('dddd'); // e.g., "Senin"

            if (strcasecmp($schedule->day, $dayName) !== 0 && strcasecmp($schedule->day, $date->englishDayOfWeek) !== 0) {
                $validator->errors()->add(
                    'appointment_date',
                    "Tanggal yang dipilih ({$dayName}) tidak sesuai dengan hari praktik dokter ({$schedule->day})."
                );
            }
        });
    }
}
