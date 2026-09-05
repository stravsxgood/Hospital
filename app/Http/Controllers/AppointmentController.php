<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Menampilkan Daftar Antrean Pasien
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $patient = $request->user()->patient;

        if (! $patient) {
            return redirect()->route('home')->with('error', 'Profil pasien tidak ditemukan.');
        }

        $appointments = Appointment::with([
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'doctorSchedule.room',
        ])
            ->where('patient_id', $patient->patient_id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('MyAppointments', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Membuat tiket antrean / reservasi baru untuk pasien.
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse|JsonResponse
    {
        $patient = $request->user()->patient;

        if (! $patient) {
            return back()->withErrors([
                'appointment_date' => 'Profil pasien tidak ditemukan. Silakan lengkapi profil terlebih dahulu.',
            ]);
        }

        try {
            $appointmentData = DB::transaction(function () use ($request, $patient) {
                // 1. Kunci baris jadwal dokter agar proses antrean terserialisasi (mencegah race condition & duplikasi nomor)
                /** @var DoctorSchedule $schedule */
                $schedule = DoctorSchedule::with(['poli', 'doctor'])
                    ->where('doctor_schedule_id', $request->doctor_schedule_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Pastikan jadwal aktif dan dokter berstatus aktif
                if ($schedule->status !== 'Aktif') {
                    throw ValidationException::withMessages([
                        'doctor_schedule_id' => 'Jadwal dokter yang dipilih sedang tidak aktif atau libur.',
                    ]);
                }

                if ($schedule->doctor && strtolower((string) $schedule->doctor->status) !== 'aktif') {
                    throw ValidationException::withMessages([
                        'doctor_schedule_id' => 'Dokter penanggung jawab saat ini sedang tidak berstatus aktif.',
                    ]);
                }

                // 3. Validasi kuota harian dokter
                $activeAppointmentsCount = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $request->appointment_date)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->count();

                if ($schedule->quota_day && $activeAppointmentsCount >= $schedule->quota_day) {
                    throw ValidationException::withMessages([
                        'appointment_date' => "Kuota pendaftaran untuk jadwal ini sudah penuh ({$schedule->quota_day}/{$schedule->quota_day}).",
                    ]);
                }

                // 4. Format nomor antrean (misal: "POL-003")
                $totalTodayCount = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $request->appointment_date)
                    ->count();

                $prefix = $schedule->poli?->kode_poli ?? 'ANT';
                $nextQueueNumber = sprintf('%s-%03d', $prefix, $totalTodayCount + 1);

                // 5. Cek jika pasien sudah memiliki riwayat di jadwal & tanggal yang sama (mencegah error constraint Postgres)
                $existingAppointment = Appointment::where('patient_id', $patient->patient_id)
                    ->where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $request->appointment_date)
                    ->first();

                if ($existingAppointment) {
                    if (in_array($existingAppointment->status, ['pending', 'confirmed', 'in_progress'], true)) {
                        throw ValidationException::withMessages([
                            'appointment_date' => 'Anda sudah memiliki tiket antrean aktif untuk jadwal dokter ini pada tanggal yang dipilih.',
                        ]);
                    }

                    // Jika tiket sebelumnya dibatalkan atau selesai, perbarui tiket tersebut dengan nomor & status baru
                    $existingAppointment->update([
                        'queue_number' => $nextQueueNumber,
                        'complaint' => $request->complaint,
                        'status' => 'pending',
                    ]);

                    $appointment = $existingAppointment;
                } else {
                    $appointment = Appointment::create([
                        'patient_id' => $patient->patient_id,
                        'doctor_schedule_id' => $schedule->doctor_schedule_id,
                        'appointment_date' => $request->appointment_date,
                        'queue_number' => $nextQueueNumber,
                        'complaint' => $request->complaint,
                        'status' => 'pending',
                    ]);
                }

                return [
                    'appointment' => $appointment,
                    'schedule' => $schedule,
                ];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Gagal membuat reservasi janji temu dokter (appointment store error)', [
                'user_id' => $request->user()?->id,
                'patient_id' => $patient->patient_id,
                'doctor_schedule_id' => $request->doctor_schedule_id,
                'appointment_date' => $request->appointment_date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'appointment_date' => 'Gagal simpan antrean: '.$e->getMessage(),
            ]);
        }

        $appointment = $appointmentData['appointment'];
        $schedule = $appointmentData['schedule'];

        $ticketPayload = [
            'appointment_id' => $appointment->appointment_id,
            'queue_number' => $appointment->queue_number,
            'doctor_name' => $schedule->doctor?->name,
            'poli_name' => $schedule->poli?->name_poli,
            'appointment_date' => Carbon::parse($appointment->appointment_date)->format('d-m-Y'),
            'patient_name' => $patient->name,
            'resident_n' => $patient->resident_n,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tiket antrean berhasil dibuat!',
                'data' => $ticketPayload,
            ]);
        }

        return redirect()->back()->with('success', [
            'message' => 'Tiket antrean berhasil dibuat!',
            'ticket' => $ticketPayload,
        ]);
    }

    /**
     * Membatalkan Antrean
     */
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $patient = $request->user()->patient;

        // Pastikan tiket milik pasien yang sedang login
        if ($appointment->patient_id !== $patient?->patient_id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan tiket ini.');
        }

        // Batalkan hanya jika statusnya masih pending atau confirmed
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Tiket dengan status ini tidak dapat dibatalkan.');
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Tiket antrean berhasil dibatalkan.');
    }
}
