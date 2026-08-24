<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminScheduleController extends Controller
{
    /**
     * Display Doctor Practice Schedule Grid & Daily Quotas.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $dayFilter = $request->query('day');
        $poliFilter = $request->query('poli_id');
        $statusFilter = $request->query('status');

        $query = DoctorSchedule::query()
            ->with([
                'doctor.specialization',
                'poli',
                'room',
            ])
            ->when($dayFilter, fn ($q) => $q->where('day', $dayFilter))
            ->when($poliFilter, fn ($q) => $q->where('poli_id', $poliFilter))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->orderByRaw("CASE day 
                WHEN 'Senin' THEN 1 
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3 
                WHEN 'Kamis' THEN 4 
                WHEN 'Jumat' THEN 5 
                WHEN 'Sabtu' THEN 6 
                ELSE 7 END")
            ->orderBy('start_time');

        $schedules = $query->paginate(20)->withQueryString();

        $doctors = Doctor::where('status', 'aktif')->orderBy('name')->get();
        $polis = Poli::where('status', 'Aktif')->orderBy('name_poli')->get();
        $rooms = Room::orderBy('name_room')->get();

        $payload = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'polis' => $polis,
            'rooms' => $rooms,
            'filters' => [
                'day' => $dayFilter,
                'poli_id' => $poliFilter,
                'status' => $statusFilter,
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Schedules retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/Facilities/Index', $payload);
    }

    /**
     * Store a newly created Doctor Schedule.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse|JsonResponse
    {
        $schedule = DoctorSchedule::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Jadwal praktik dokter berhasil ditambahkan.',
                'data' => $schedule,
            ], 201);
        }

        return redirect()->back()->with('success', 'Jadwal praktik dokter berhasil ditambahkan.');
    }

    /**
     * Update the specified Doctor Schedule.
     */
    public function update(UpdateScheduleRequest $request, DoctorSchedule $schedule): RedirectResponse|JsonResponse
    {
        $schedule->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Jadwal praktik dokter berhasil diperbarui.',
                'data' => $schedule,
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal praktik dokter berhasil diperbarui.');
    }

    /**
     * Remove the specified Doctor Schedule safely.
     */
    public function destroy(Request $request, DoctorSchedule $schedule): RedirectResponse|JsonResponse
    {
        // Cek apakah ada antrean aktif di hari ini / masa depan
        $hasActiveQueues = Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)
            ->whereDate('appointment_date', '>=', Carbon::today()->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->exists();

        if ($hasActiveQueues) {
            $msg = 'Jadwal tidak dapat dihapus karena terdapat pasien aktif yang telah terdaftar dalam antrean.';
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $schedule->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Jadwal praktik berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal praktik berhasil dihapus.');
    }
}
