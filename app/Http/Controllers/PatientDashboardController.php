<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientDashboardController extends Controller
{
    /**
     * Menampilkan ringkasan portal dashboard pasien.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $patient = $user->patient;

        $today = Carbon::today()->toDateString();

        // 1. Ambil antrean aktif pasien (hari ini / mendatang yang belum selesai)
        $activeAppointment = null;
        $totalVisits = 0;
        $upcomingCount = 0;
        $completedCount = 0;
        $recentAppointments = [];

        if ($patient) {
            $patientId = $patient->patient_id;

            $activeAppointment = Appointment::with([
                'doctorSchedule.doctor.specialization',
                'doctorSchedule.poli',
                'doctorSchedule.room',
            ])
                ->where('patient_id', $patientId)
                ->whereDate('appointment_date', '>=', $today)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->orderBy('appointment_date', 'asc')
                ->first();

            // Hitung statistik pasien
            $totalVisits = Appointment::where('patient_id', $patientId)->count();
            $upcomingCount = Appointment::where('patient_id', $patientId)
                ->whereDate('appointment_date', '>=', $today)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->count();
            $completedCount = Appointment::where('patient_id', $patientId)
                ->where('status', 'completed')
                ->count();

            // 5 Riwayat kunjungan terakhir
            $recentAppointments = Appointment::with([
                'doctorSchedule.doctor',
                'doctorSchedule.poli',
                'doctorSchedule.room',
            ])
                ->where('patient_id', $patientId)
                ->latest('appointment_date')
                ->take(5)
                ->get();
        }

        // 2. Ambil rekomendasi jadwal dokter yang aktif hari ini
        $availableSchedules = DoctorSchedule::with(['doctor', 'poli', 'room'])
            ->where('status', 'Aktif')
            ->take(3)
            ->get();

        return Inertia::render('patient/PatientDashboard', [
            'patientName' => $patient?->name ?? $user->name,
            'stats' => [
                'total_visits' => $totalVisits,
                'upcoming' => $upcomingCount,
                'completed' => $completedCount,
            ],
            'activeAppointment' => $activeAppointment,
            'recentAppointments' => $recentAppointments,
            'availableSchedules' => $availableSchedules,
            'currentDate' => Carbon::today()->translatedFormat('l, d F Y'),
        ]);
    }
}
