<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{

    // Menampilkan Daftar Antrean Pasien
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


    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $patient = $request->user()->patient;

        if (! $patient) {
            return back()->with('error', 'Profil pasien tidak ditemukan. Silakan lengkapi profil terlebih dahulu.');
        }

        try {
            $appointmentData = DB::transaction(function () use ($request, $patient) {
                // 1. Kunci baris jadwal dokter agar proses antrean terserialisasi (mencegah duplikasi nomor)
                $schedule = DoctorSchedule::with('poli', 'doctor')
                    ->where('doctor_schedule_id', $request->doctor_schedule_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Cek apakah pasien sudah memiliki tiket aktif di jadwal & tanggal yang sama
                $hasActiveAppointment = Appointment::where('patient_id', $patient->patient_id)
                    ->where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $request->appointment_date)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->exists();

                if ($hasActiveAppointment) {
                    throw ValidationException::withMessages([
                        'appointment_date' => 'Anda sudah memiliki tiket antrean aktif untuk jadwal dokter ini pada tanggal yang dipilih.',
                    ]);
                }

                // 3. Hitung jumlah antrean (tanpa lockForUpdate pada query count)
                $currentCount = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $request->appointment_date)
                    ->count();

                // 4. Validasi Kuota Harian (quota_day)
                if ($schedule->quota_day && $currentCount >= $schedule->quota_day) {
                    throw ValidationException::withMessages([
                        'appointment_date' => "Kuota pendaftaran untuk jadwal ini sudah penuh ({$schedule->quota_day}/{$schedule->quota_day}).",
                    ]);
                }

                // 5. Format nomor antrean (misal: "POL-003")
                $prefix = $schedule->poli?->kode_poli ?? 'ANT';
                $nextQueueNumber = sprintf('%s-%03d', $prefix, $currentCount + 1);

                // 6. Buat data reservasi
                $appointment = Appointment::create([
                    'patient_id'         => $patient->patient_id,
                    'doctor_schedule_id' => $schedule->doctor_schedule_id,
                    'appointment_date'   => $request->appointment_date,
                    'queue_number'       => $nextQueueNumber,
                    'complaint'          => $request->complaint,
                    'status'             => 'pending',
                ]);

                return [
                    'appointment' => $appointment,
                    'schedule'    => $schedule,
                ];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withErrors([
                'appointment_date' => 'Gagal simpan antrean: ' . $e->getMessage(),
            ]);
        }

        $appointment = $appointmentData['appointment'];
        $schedule = $appointmentData['schedule'];

        return redirect()->back()->with('success', [
            'message' => 'Tiket antrean berhasil dibuat!',
            'ticket' => [
                'appointment_id'   => $appointment->appointment_id,
                'queue_number'     => $appointment->queue_number,
                'doctor_name'      => $schedule->doctor?->name,
                'poli_name'        => $schedule->poli?->name_poli ?? $schedule->poli?->name,
                'appointment_date' => Carbon::parse($appointment->appointment_date)->format('d-m-Y'),
                'patient_name'     => $patient->name,
                'resident_n'       => $patient->resident_n,
            ],
        ]);
    }

    // Membatalkan Antrean
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $patient = $request->user()->patient;

        // Pastikan tiket milik pasien yang sedang login
        if ($appointment->patient_id !== $patient?->patient_id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan tiket ini.');
        }

        // Batalkan hanya jika statusnya masih pending atau confirmed
        if (! in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Tiket dengan status ini tidak dapat dibatalkan.');
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Tiket antrean berhasil dibatalkan.');
    }

    /**
     * Menghapus riwayat kunjungan yang sudah selesai atau dibatalkan.
     * Hanya appointment dengan status 'completed' atau 'cancelled' yang dapat dihapus.
     * Memverifikasi kepemilikan tiket berdasarkan patient_id pengguna yang login.
     */
    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $patient = $request->user()->patient;

        // Pastikan tiket milik pasien yang sedang login
        if ($appointment->patient_id !== $patient?->patient_id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus riwayat ini.');
        }

        // Hanya izinkan penghapusan riwayat (completed / cancelled)
        if (! in_array($appointment->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Hanya riwayat kunjungan yang sudah selesai atau dibatalkan yang dapat dihapus.');
        }

        $appointment->delete();

        return back()->with('success', 'Riwayat kunjungan berhasil dihapus.');
    }
}