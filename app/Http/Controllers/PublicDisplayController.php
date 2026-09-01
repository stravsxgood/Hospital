<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppSetting;
use App\Models\DisplayVideo;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PublicDisplayController extends Controller
{
    /**
     * Tampilan utama layar monitor TV ruang tunggu.
     */
    public function index(): Response
    {
        $videos = DisplayVideo::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get(['id', 'title', 'file_path', 'order', 'is_active']);

        return Inertia::render('Display/Index', [
            'initialData' => $this->getDisplayData(),
            'currentDate' => Carbon::today()->translatedFormat('l, d F Y'),
            'videos' => $videos,
            'displayConfig' => [
                'hospital_name' => AppSetting::get('display.hospital_name', 'Hospital Population'),
                'scroll_speed' => (int) AppSetting::get('display.scroll_speed', '5000'),
                'show_patient_name' => AppSetting::get('display.show_patient_name', 'true') === 'true',
                'theme' => AppSetting::get('display.theme', 'evergreen'),
                'announcement_text' => AppSetting::get('display.announcement_text', ''),
            ],
        ]);
    }

    /**
     * Endpoint API ringan untuk polling pembaruan data antrean.
     */
    public function liveData(): JsonResponse
    {
        return response()->json($this->getDisplayData());
    }

    /**
     * Mengambil matriks antrean yang sedang aktif diperiksa di setiap poliklinik.
     */
    private function getDisplayData(): array
    {
        $today = Carbon::today()->toDateString();

        // 1. Ambil semua jadwal dokter yang aktif
        $schedules = DoctorSchedule::with(['doctor.specialization', 'poli', 'room'])
            ->where('status', 'Aktif')
            ->get();

        // 2. Ambil seluruh poliklinik beserta pasien yang sedang aktif diperiksa / menunggu
        $clinics = $schedules->map(function ($sch) {
            // Pasien yang sedang in_progress di poliklinik ini
            $current = Appointment::with('patient')
                ->where('doctor_schedule_id', $sch->doctor_schedule_id)
                ->where('status', 'in_progress')
                ->latest('updated_at')
                ->first();

            // Pasien berikutnya yang sedang menunggu giliran
            $next = Appointment::with('patient')
                ->where('doctor_schedule_id', $sch->doctor_schedule_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('appointment_date', 'asc')
                ->orderBy('queue_number', 'asc')
                ->first();

            // Total antrean menunggu
            $waitingCount = Appointment::where('doctor_schedule_id', $sch->doctor_schedule_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            return [
                'schedule_id' => $sch->doctor_schedule_id,
                'doctor_name' => $sch->doctor?->name ?? 'Dokter',
                'poli_name' => $sch->poli?->name_poli ?? $sch->poli?->name ?? 'Poliklinik',
                'room_name' => $sch->room?->name_room ?? 'Ruang Periksa',
                'current_calling' => $current?->queue_number ?? null,
                'patient_name' => $current?->patient?->name ?? null,
                'next_calling' => $next?->queue_number ?? '-',
                'waiting_count' => $waitingCount,
            ];
        });

        // 3. Ambil pasien yang paling baru dipanggil (in_progress) sebagai fokus utama layar
        $latestCalled = Appointment::with([
            'patient',
            'doctorSchedule.doctor',
            'doctorSchedule.poli',
            'doctorSchedule.room',
        ])
            ->where('status', 'in_progress')
            ->latest('updated_at')
            ->first();

        return [
            'clinics' => $clinics,
            'latestCalled' => $latestCalled ? [
                'appointment_id' => $latestCalled->appointment_id,
                'queue_number' => $latestCalled->queue_number,
                'patient_name' => $latestCalled->patient?->name ?? 'Pasien',
                'poli_name' => $latestCalled->doctorSchedule?->poli?->name_poli ?? $latestCalled->doctorSchedule?->poli?->name ?? 'Poliklinik',
                'room_name' => $latestCalled->doctorSchedule?->room?->name_room ?? 'Ruang Periksa',
                'doctor_name' => $latestCalled->doctorSchedule?->doctor?->name ?? 'Dokter',
                'updated_at' => $latestCalled->updated_at ? $latestCalled->updated_at->toIso8601String() : now()->toIso8601String(),
            ] : null,
        ];
    }
}
