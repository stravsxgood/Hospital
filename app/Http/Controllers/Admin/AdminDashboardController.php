<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\DisplayVideo;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display the Super Admin Executive Governance & Financial Dashboard.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateTimeString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $dayNameIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][Carbon::now()->dayOfWeek];

        // 1. Financial KPI (Agregasi Pendapatan & Metode Pembayaran)
        $todayRevenue = (float) Billing::where('status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $weekRevenue = (float) Billing::where('status', 'paid')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('total_amount');

        $monthRevenue = (float) Billing::where('status', 'paid')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $revenueByMethod = Billing::where('status', 'paid')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($item) => [
                'method' => $item->payment_method ?? 'cash',
                'label' => match ($item->payment_method) {
                    'xendit_qris' => 'Xendit QRIS',
                    'edc' => 'Mesin EDC',
                    'xendit_va', 'transfer' => 'Virtual Account',
                    default => 'Tunai (Cash)',
                },
                'total' => (float) $item->total,
                'count' => (int) $item->count,
            ]);

        // Monthly Trend (6 Bulan Terakhir)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth()->toDateTimeString();
            $monthEnd = $month->copy()->endOfMonth()->toDateTimeString();

            $sum = (float) Billing::where('status', 'paid')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $monthlyTrend[] = [
                'month' => $month->translatedFormat('M Y'),
                'revenue' => $sum,
            ];
        }

        // 2. Clinical & Morbidity Statistics (Top 10 Diagnosis Kasus)
        $topDiagnoses = MedicalRecord::select('assessment', DB::raw('COUNT(*) as case_count'))
            ->whereNotNull('assessment')
            ->where('assessment', '!=', '')
            ->groupBy('assessment')
            ->orderByDesc('case_count')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'diagnosis' => $item->assessment,
                'case_count' => (int) $item->case_count,
            ]);

        // 3. Operational Clinic & Queue Statistics
        $todayActiveQueues = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        $todayCompletedQueues = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $doctorsOnDutyCount = DoctorSchedule::where('day', $dayNameIndo)
            ->where('status', 'Aktif')
            ->distinct('doctor_id')
            ->count('doctor_id');

        // Matriks Poliklinik Hari Ini
        $clinicMatrix = Poli::with([
            'schedules' => function ($query) use ($dayNameIndo) {
                $query->where('day', $dayNameIndo)
                    ->where('status', 'Aktif')
                    ->with(['doctor:doctor_id,name', 'room:room_id,name_room,code_room']);
            },
        ])->get()->map(function ($poli) use ($today) {
            $schedule = $poli->schedules->first();
            $waitingCount = 0;
            if ($schedule) {
                $waitingCount = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
                    ->whereDate('appointment_date', $today)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->count();
            }

            return [
                'poli_id' => $poli->poli_id,
                'name_poli' => $poli->name_poli,
                'kode_poli' => $poli->kode_poli,
                'location' => $poli->location,
                'doctor_name' => $schedule?->doctor?->name ?? 'Tidak Ada Praktik',
                'room_name' => $schedule?->room?->name_room ?? '-',
                'waiting_count' => $waitingCount,
                'is_active_today' => $schedule !== null,
            ];
        });

        // 4. Staff & Demographics Summary
        $staffStats = [
            'total_users' => User::count(),
            'total_doctors' => Doctor::count(),
            'doctors_active' => Doctor::where('status', 'aktif')->count(),
            'total_nurses' => Nurse::count(),
            'nurses_tetap' => Nurse::where('type', 'tetap')->count(),
            'nurses_koas' => Nurse::where('type', 'koas')->count(),
            'total_patients' => Patient::count(),
            'total_polis' => Poli::count(),
            'total_inactive' => User::where('is_active', false)->count(),
            'users_never_logged_in' => User::whereNull('last_login_at')
                ->where('created_at', '<', Carbon::now()->subDays(7))
                ->count(),
            'users_last_7_days' => User::where('last_login_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];

        $payload = [
            'financial' => [
                'today_revenue' => $todayRevenue,
                'week_revenue' => $weekRevenue,
                'month_revenue' => $monthRevenue,
                'revenue_by_method' => $revenueByMethod,
                'monthly_trend' => $monthlyTrend,
            ],
            'morbidity' => [
                'top_diagnoses' => $topDiagnoses,
                'today_consultations_count' => MedicalRecord::whereDate('created_at', $today)->count(),
            ],
            'operational' => [
                'today_active_queues' => $todayActiveQueues,
                'today_completed_queues' => $todayCompletedQueues,
                'doctors_on_duty_count' => $doctorsOnDutyCount,
                'clinic_matrix' => $clinicMatrix,
            ],
            'staff_stats' => $staffStats,
            'display_videos' => DisplayVideo::orderBy('order', 'asc')->latest()->get(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Super admin dashboard metrics retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/Dashboard', $payload);
    }
}
