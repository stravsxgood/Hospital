<?php

namespace App\Http\Requests;

use App\Models\DoctorSchedule;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'patient';
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctor,doctor_id',
            'poli_id' => 'required|exists:poli,poli_id',
            'registration_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $patientId = $this->user()->patient->patient_id;
            $regDate = $this->input('registration_date');
            $doctorId = $this->input('doctor_id');

            // 1. Cek apakah pasien sudah mendaftar di dokter yang sama pada hari tersebut
            $alreadyBooked = Registration::where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->whereDate('registration_date', $regDate)
                ->whereNotIn('status', ['Batal'])
                ->exists();

            if ($alreadyBooked) {
                $validator->errors()->add('registration_date', 'Anda sudah memiliki antrean aktif dengan dokter ini pada tanggal yang dipilih.');
                return;
            }

            // 2. Cek hari praktik dan sisa kuota harian dokter
            $dayName = Carbon::parse($regDate)->locale('id')->isoFormat('dddd');
            $schedule = DoctorSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayName)
                ->where('status', 'Aktif')
                ->first();

            if (!$schedule) {
                $validator->errors()->add('doctor_id', "Dokter tidak memiliki jadwal praktik aktif pada hari {$dayName}.");
                return;
            }

            $currentBookedCount = Registration::where('doctor_id', $doctorId)
                ->whereDate('registration_date', $regDate)
                ->whereNotIn('status', ['Batal'])
                ->count();

            if ($currentBookedCount >= $schedule->quota_day) {
                $validator->errors()->add('quota', 'Kuota antrean dokter untuk tanggal tersebut sudah penuh.');
            }
        });
    }
}
