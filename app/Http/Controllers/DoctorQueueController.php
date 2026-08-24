<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorQueueController extends Controller
{
    /**
     * Menampilkan papan antrean pasien untuk dokter / poliklinik.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::today()->toDateString();
        $user = $request->user();
        $doctor = $user?->doctor;

        // 1. Ambil semua jadwal dokter yang aktif
        $allSchedules = DoctorSchedule::with(['doctor.specialization', 'poli', 'room'])
            ->where('status', 'Aktif')
            ->get();

        // Jika user adalah dokter, dahulukan/filter jadwal miliknya
        $doctorSchedules = $doctor
            ? $allSchedules->where('doctor_id', $doctor->doctor_id)->values()
            : collect();

        // Daftar jadwal yang ditampilkan di dropdown (prioritaskan jadwal dokter login jika ada)
        $schedules = ($doctorSchedules->isNotEmpty())
            ? $doctorSchedules
            : $allSchedules;

        // 2. Tentukan jadwal yang sedang dipilih
        $selectedScheduleId = (int) $request->query('schedule_id', $schedules->first()?->doctor_schedule_id);
        $selectedSchedule = $allSchedules->firstWhere('doctor_schedule_id', $selectedScheduleId) ?? $schedules->first();
        $selectedScheduleId = $selectedSchedule?->doctor_schedule_id;

        // 3. Cari daftar tanggal yang memiliki data reservasi pada jadwal terpilih
        $availableDates = [];
        if ($selectedScheduleId) {
            $availableDates = Appointment::where('doctor_schedule_id', $selectedScheduleId)
                ->selectRaw('appointment_date, COUNT(*) as count')
                ->groupBy('appointment_date')
                ->orderBy('appointment_date', 'asc')
                ->get()
                ->map(function ($row) {
                    $d = Carbon::parse($row->appointment_date);
                    return [
                        'date'     => $d->toDateString(),
                        'label'    => $d->translatedFormat('D, d M Y'),
                        'count'    => (int) $row->count,
                        'is_today' => $d->isToday(),
                    ];
                })
                ->values()
                ->toArray();
        }

        // 4. Tentukan filter tanggal yang aktif
        $selectedDate = $request->query('date');
        if ($selectedDate === null) {
            if (! empty($availableDates)) {
                $hasToday = collect($availableDates)->firstWhere('date', $today);
                if ($hasToday) {
                    $selectedDate = $today;
                } else {
                    // Cari tanggal mendatang terdekat, atau tanggal pertama
                    $upcoming = collect($availableDates)->first(fn ($item) => $item['date'] >= $today);
                    $selectedDate = $upcoming['date'] ?? $availableDates[0]['date'];
                }
            } else {
                $selectedDate = $today;
            }
        }

        // 5. Ambil antrean untuk jadwal & tanggal tersebut
        $appointments = [];
        if ($selectedScheduleId) {
            $appointmentsQuery = Appointment::with([
                'patient',
                'doctorSchedule.doctor.specialization',
                'doctorSchedule.poli',
                'doctorSchedule.room',
            ])
                ->where('doctor_schedule_id', $selectedScheduleId);

            if ($selectedDate !== 'all') {
                $appointmentsQuery->whereDate('appointment_date', $selectedDate);
            }

            $appointments = $appointmentsQuery->orderByRaw("
                    CASE 
                        WHEN status = 'in_progress' THEN 1
                        WHEN status = 'pending' THEN 2
                        WHEN status = 'confirmed' THEN 3
                        WHEN status = 'completed' THEN 4
                        WHEN status = 'cancelled' THEN 5
                        ELSE 6
                    END
                ")
                ->orderBy('appointment_date', 'asc')
                ->orderBy('queue_number', 'asc')
                ->get();
        }

        return Inertia::render('doctor/QueueBoard', [
            'schedules'        => $schedules,
            'allSchedules'     => $allSchedules,
            'selectedSchedule' => $selectedSchedule,
            'appointments'     => $appointments,
            'availableDates'   => $availableDates,
            'selectedDate'     => $selectedDate,
            'todayDate'        => $today,
            'currentDate'      => Carbon::today()->translatedFormat('l, d F Y'),
            'isDoctor'         => (bool) $doctor,
            'doctorName'       => $doctor?->name,
        ]);
    }

    /**
     * Panggil pasien ke ruang periksa (status: in_progress).
     * Dapat dipanggil berulang kali untuk membunyikan bel/suara display TV kembali.
     */
    public function callPatient(Appointment $appointment): RedirectResponse
    {
        // Pasien lain di jadwal ini yang masih in_progress diubah kembali ke pending
        Appointment::where('doctor_schedule_id', $appointment->doctor_schedule_id)
            ->where('status', 'in_progress')
            ->where('appointment_id', '!=', $appointment->appointment_id)
            ->update(['status' => 'pending']);

        // Set status in_progress dan perbarui updated_at secara eksplisit
        $appointment->status = 'in_progress';
        $appointment->updated_at = now();
        $appointment->save();

        // Eager load relasi untuk payload siaran real-time
        $appointment->loadMissing(['patient', 'doctorSchedule.doctor', 'doctorSchedule.poli', 'doctorSchedule.room']);

        $patientName = $appointment->patient?->name ?? 'Pasien';
        $poliName = $appointment->doctorSchedule?->poli?->name ?? 'Poliklinik';
        $roomName = $appointment->doctorSchedule?->room?->name ?? 'Ruang Periksa';
        $doctorName = $appointment->doctorSchedule?->doctor?->name ?? 'Dokter Spesialis';
        $doctorId = (int) ($appointment->doctorSchedule?->doctor_id ?? 0);
        $cleanQueue = str_replace('-', ' ', $appointment->queue_number);
        $voiceText = "Nomor antrean {$cleanQueue}, atas nama {$patientName}, silakan menuju ke {$poliName}, {$roomName}.";

        // Siarkan pemanggilan pasien via Laravel Reverb WebSockets
        event(new \App\Events\PatientCalledEvent(
            appointmentId: (int) $appointment->appointment_id,
            queueNumber: (string) $appointment->queue_number,
            patientName: (string) $patientName,
            poliName: (string) $poliName,
            roomName: (string) $roomName,
            doctorName: (string) $doctorName,
            doctorId: $doctorId,
            voiceText: $voiceText,
            calledAt: now()->toIso8601String()
        ));

        return back()->with('success', "Memanggil pasien nomor antrean {$appointment->queue_number}.");
    }

    /**
     * Selesaikan sesi konsultasi pasien (status: completed).
     */
    public function completeConsultation(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update([
            'status' => 'completed',
        ]);

        return back()->with('success', "Konsultasi pasien {$appointment->queue_number} telah selesai.");
    }

    /**
     * Lewati pasien jika tidak merespons (status kembali ke pending atau ditandai).
     */
    public function skipPatient(Appointment $appointment): RedirectResponse
    {
        $appointment->update([
            'status' => 'pending',
        ]);

        return back()->with('success', "Nomor antrean {$appointment->queue_number} telah dilewati.");
    }
}