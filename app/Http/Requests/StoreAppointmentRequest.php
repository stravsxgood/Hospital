<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Jika user adalah pasien namun profil tabel patient belum terbuat, buat otomatis profil dasar
        if ($user->patient === null && ($user->role === 'patient' || $user->hasRole('patient'))) {
            Patient::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'resident_n' => 'AUTO-'.str_pad((string) $user->id, 8, '0', STR_PAD_LEFT),
                'gender' => 'Laki-laki',
                'birthday_date' => '2000-01-01',
                'status' => 'active',
                'registration_date' => now()->toDateString(),
            ]);

            $user->load('patient');
        }

        return $user->patient !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'doctor_schedule_id' => ['required', 'integer', 'exists:doctor_schedule,doctor_schedule_id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'complaint' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_schedule_id.required' => 'Jadwal dokter wajib dipilih.',
            'doctor_schedule_id.exists' => 'Jadwal dokter yang dipilih tidak valid atau tidak ditemukan.',
            'appointment_date.required' => 'Tanggal rencana kunjungan wajib diisi.',
            'appointment_date.date' => 'Format tanggal kunjungan tidak valid.',
            'appointment_date.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini.',
            'complaint.max' => 'Keluhan tidak boleh lebih dari 500 karakter.',
        ];
    }

    /**
     * Configure additional validator logic.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var DoctorSchedule|null $schedule */
            $schedule = DoctorSchedule::with(['doctor', 'poli'])->find($this->doctor_schedule_id);

            if (! $schedule) {
                $validator->errors()->add('doctor_schedule_id', 'Jadwal dokter tidak ditemukan.');

                return;
            }

            // 1. Validasi status keaktifan jadwal dokter
            if ($schedule->status !== 'Aktif') {
                $validator->errors()->add(
                    'doctor_schedule_id',
                    'Jadwal praktik dokter yang dipilih saat ini sedang libur / tidak aktif.'
                );

                return;
            }

            // 2. Validasi status dokter
            if ($schedule->doctor && strtolower((string) $schedule->doctor->status) !== 'aktif') {
                $validator->errors()->add(
                    'doctor_schedule_id',
                    'Dokter penanggung jawab saat ini sedang tidak berstatus aktif.'
                );

                return;
            }

            // 3. Validasi hari kunjungan harus sesuai dengan jadwal praktik dokter
            $date = Carbon::parse($this->appointment_date);
            $dayName = $date->locale('id')->isoFormat('dddd'); // misal: "Senin", "Selasa"

            if (strcasecmp($schedule->day, $dayName) !== 0 && strcasecmp($schedule->day, $date->englishDayOfWeek) !== 0) {
                $validator->errors()->add(
                    'appointment_date',
                    "Tanggal yang dipilih ({$dayName}) tidak sesuai dengan hari praktik dokter ({$schedule->day}). Silakan pilih tanggal yang jatuh pada hari {$schedule->day}."
                );

                return;
            }

            // 4. Validasi kuota harian dokter
            $currentCount = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
                ->whereDate('appointment_date', $this->appointment_date)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->count();

            if ($schedule->quota_day && $currentCount >= $schedule->quota_day) {
                $validator->errors()->add(
                    'appointment_date',
                    "Kuota antrean untuk jadwal ini pada tanggal tersebut sudah penuh ({$schedule->quota_day}/{$schedule->quota_day})."
                );

                return;
            }

            // 5. Cek apakah pasien sudah memiliki tiket antrean aktif di jadwal & tanggal yang sama
            $patient = $this->user()?->patient;
            if ($patient) {
                $hasActiveAppointment = Appointment::where('patient_id', $patient->patient_id)
                    ->where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $this->appointment_date)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->exists();

                if ($hasActiveAppointment) {
                    $validator->errors()->add(
                        'appointment_date',
                        'Anda sudah memiliki tiket antrean aktif untuk jadwal dokter ini pada tanggal yang dipilih.'
                    );
                }
            }
        });
    }
}
