<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePoliRequest;
use App\Http\Requests\Admin\UpdatePoliRequest;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPoliController extends Controller
{
    /**
     * Display Poliklinik & Facility Management.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $polis = Poli::withCount(['schedules', 'doctors'])
            ->orderBy('name_poli')
            ->get();

        $rooms = Room::withCount('schedules')
            ->orderBy('floor')
            ->orderBy('name_room')
            ->get();

        $payload = [
            'polis' => $polis,
            'rooms' => $rooms,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Poliklinik data retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/Facilities/Index', $payload);
    }

    /**
     * Store a newly created Poliklinik.
     */
    public function store(StorePoliRequest $request): RedirectResponse|JsonResponse
    {
        $poli = Poli::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Poliklinik {$poli->name_poli} berhasil ditambahkan.",
                'data' => $poli,
            ], 201);
        }

        return redirect()->back()->with('success', "Poliklinik {$poli->name_poli} berhasil ditambahkan.");
    }

    /**
     * Update the specified Poliklinik.
     */
    public function update(UpdatePoliRequest $request, Poli $poli): RedirectResponse|JsonResponse
    {
        $poli->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Data Poliklinik {$poli->name_poli} berhasil diperbarui.",
                'data' => $poli,
            ]);
        }

        return redirect()->back()->with('success', "Data Poliklinik {$poli->name_poli} berhasil diperbarui.");
    }

    /**
     * Remove the specified Poliklinik from storage safely.
     */
    public function destroy(Request $request, Poli $poli): RedirectResponse|JsonResponse
    {
        // Safe deletion check: jika memiliki jadwal dokter aktif, tolak penghapusan
        $hasSchedules = DoctorSchedule::where('poli_id', $poli->poli_id)->exists();
        if ($hasSchedules) {
            $msg = "Poliklinik {$poli->name_poli} tidak dapat dihapus karena masih memiliki jadwal dokter terhubung.";
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $poliName = $poli->name_poli;
        $poli->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Poliklinik {$poliName} berhasil dihapus.",
            ]);
        }

        return redirect()->back()->with('success', "Poliklinik {$poliName} berhasil dihapus.");
    }
}
