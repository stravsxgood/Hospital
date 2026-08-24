<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\DoctorSchedule;
use App\Models\Medicine;
use App\Models\Poli;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class StaffDashboardController
 *
 * Pusat Komando Operasional & Meja Depan Rumah Sakit (Front-Office, Farmasi, & Billing POS).
 * Mengagregasi metrik KPI, verifikasi kedatangan pasien, antrean resep farmasi,
 * dan peringatan kritis inventori obat tanpa N+1 query.
 */
class StaffDashboardController extends Controller
{
    /**
     * Menampilkan dashboard operasional terpadu staf & perawat.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::today()->toDateString();
        $selectedDate = $request->query('date', $today);

        // 1. Agregasi KPI Operasional via PostgreSQL Aggregates
        $totalToday = Appointment::whereDate('appointment_date', $today)->count();
        $waitingConfirmationCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->count();
        $confirmedCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();
        $inProgressCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'in_progress')
            ->count();
        $completedCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();
        $cancelledCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'cancelled')
            ->count();

        $totalUpcoming = Appointment::whereDate('appointment_date', '>=', $today)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();
        $totalAll = Appointment::count();

        // 2. Agregasi KPI Farmasi & Billing
        $pendingPrescriptionsCount = Prescription::whereIn('status', ['menunggu', 'diproses'])->count();
        $outOfStockCount = Medicine::outOfStock()->count();
        $lowStockCount = Medicine::lowStock(10)->count();
        $unpaidBillingsCount = Billing::whereIn('status', ['unpaid', 'pending'])->count();
        $todayRevenue = (float) Billing::where('status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('total_amount');

        // 3. Jadwal Praktik Dokter Aktif
        $activeSchedules = DoctorSchedule::with(['doctor.specialization', 'poli', 'room'])
            ->where('status', 'Aktif')
            ->get();

        $totalQuotaToday = $activeSchedules->sum('quota_day') ?: 1;
        $quotaPercentage = min(100, (int) round(($totalToday / $totalQuotaToday) * 100));

        // 4. Eager Load Antrean Pasien Hari Ini (Prioritaskan yang belum check-in / pending)
        $todayAppointments = Appointment::with([
            'patient',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'doctorSchedule.room',
            'medicalRecord.prescription.items.medicine',
            'billing',
        ])
            ->whereDate('appointment_date', $selectedDate)
            ->orderByRaw("
                CASE 
                    WHEN status = 'pending' THEN 1 
                    WHEN status = 'confirmed' THEN 2 
                    WHEN status = 'in_progress' THEN 3 
                    WHEN status = 'completed' THEN 4 
                    ELSE 5 
                END
            ")
            ->orderBy('appointment_id', 'asc')
            ->get();

        // 5. Matriks Poliklinik & Beban Ruang Periksa
        $clinicMatrix = $activeSchedules->map(function ($sch) use ($todayAppointments) {
            $schTodayAppointments = $todayAppointments->where('doctor_schedule_id', $sch->doctor_schedule_id);

            $currentServing = $schTodayAppointments->firstWhere('status', 'in_progress');
            $waiting = $schTodayAppointments->whereIn('status', ['pending', 'confirmed'])->count();
            $completed = $schTodayAppointments->where('status', 'completed')->count();
            $totalTodayClinic = $schTodayAppointments->count();

            return [
                'schedule_id' => $sch->doctor_schedule_id,
                'doctor_name' => $sch->doctor?->name ?? 'Dokter Spesialis',
                'specialization' => $sch->doctor?->specialization?->name_specialization ?? 'Umum',
                'poli_name' => $sch->poli?->name_poli ?? $sch->poli?->name ?? 'Poliklinik',
                'room_name' => $sch->room?->name_room ?? 'Ruang Periksa',
                'start_time' => substr((string) $sch->start_time, 0, 5),
                'end_time' => substr((string) $sch->end_time, 0, 5),
                'current_calling' => $currentServing?->queue_number ?? null,
                'waiting_count' => $waiting,
                'completed_count' => $completed,
                'total_patients' => $totalTodayClinic,
                'quota_day' => $sch->quota_day ?? 30,
            ];
        });

        // 6. Antrean Resep Obat Farmasi (Pending / Sedang Diracik)
        $pendingPrescriptions = Prescription::with([
            'medicalRecord.patient',
            'medicalRecord.doctor.specialization',
            'medicalRecord.reservation.doctorSchedule.poli',
            'items.medicine',
        ])
            ->whereIn('status', ['menunggu', 'diproses'])
            ->latest('created_at')
            ->take(10)
            ->get();

        // 7. Peringatan Obat Kritis (Stok Habis & Stok Menipis <= 10)
        $criticalMedicines = Medicine::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        // 8. Tren Kunjungan Mingguan (7 Hari Terakhir)
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $count = Appointment::whereDate('appointment_date', $dateStr)->count();

            $weeklyTrend[] = [
                'day' => $date->translatedFormat('D'),
                'date' => $date->format('d/m'),
                'count' => $count,
            ];
        }

        // 9. Distribusi Pasien per Poliklinik
        $poliList = Poli::all();
        $poliDistribution = $poliList->map(function ($poli) use ($today, $totalToday) {
            $count = Appointment::whereHas('doctorSchedule', function ($query) use ($poli) {
                $query->where('poli_id', $poli->poli_id);
            })->whereDate('appointment_date', $today)->count();

            $percent = $totalToday > 0 ? (int) round(($count / $totalToday) * 100) : 0;

            return [
                'poli_name' => $poli->name_poli ?? $poli->name ?? 'Poliklinik',
                'count' => $count,
                'percent' => $percent,
            ];
        })->filter(fn ($p) => $p['count'] > 0)->values();

        if ($poliDistribution->isEmpty()) {
            $poliDistribution = $poliList->take(3)->map(fn ($p) => [
                'poli_name' => $p->name_poli ?? $p->name ?? 'Poliklinik',
                'count' => 0,
                'percent' => 0,
            ]);
        }

        // 10. Log Aktivitas & Antrean Terkini
        $recentAppointments = Appointment::with([
            'patient',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'doctorSchedule.room',
            'medicalRecord.prescription.items.medicine',
            'billing',
        ])
            ->latest('created_at')
            ->take(15)
            ->get();

        return Inertia::render('StaffDashboard', [
            'stats' => [
                'total' => $totalToday,
                'total_upcoming' => $totalUpcoming,
                'total_all' => $totalAll,
                'waiting_confirmation' => $waitingConfirmationCount,
                'confirmed' => $confirmedCount,
                'in_progress' => $inProgressCount,
                'completed' => $completedCount,
                'cancelled' => $cancelledCount,
                'pending_prescriptions' => $pendingPrescriptionsCount,
                'out_of_stock_medicines' => $outOfStockCount,
                'low_stock_medicines' => $lowStockCount,
                'unpaid_billings' => $unpaidBillingsCount,
                'today_revenue' => $todayRevenue,
                'quota_percentage' => $quotaPercentage,
            ],
            'todayQueue' => $todayAppointments,
            'recentAppointments' => $recentAppointments,
            'pendingPrescriptions' => $pendingPrescriptions,
            'criticalMedicines' => $criticalMedicines,
            'clinicMatrix' => $clinicMatrix,
            'weeklyTrend' => $weeklyTrend,
            'poliDistribution' => $poliDistribution,
            'currentDate' => Carbon::today()->translatedFormat('l, d F Y'),
            'selectedDate' => $selectedDate,
        ]);
    }
}
