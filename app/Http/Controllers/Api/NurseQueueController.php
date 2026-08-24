<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NurseQueueController extends Controller
{
    /**
     * Menampilkan daftar antrean pasien hari ini
     */
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        $queues = Registration::with(['patient', 'doctor.specialization', 'poli'])
            ->whereDate('registration_date', $today)
            ->when($request->query('poli_id'), function ($query, $poliId) {
                $query->where('poli_id', $poliId);
            })
            ->when($request->query('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('registration_id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'date' => $today,
            'total' => $queues->count(),
            'data' => $queues,
        ]);
    }

    /**
     * Verifikasi kehadiran pasien dan terbitkan nomor antrean
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $nurse = $request->user()->nurse;

        if (!$nurse) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil perawat tidak ditemukan untuk akun ini.',
            ], 403);
        }

        $registration = Registration::findOrFail($id);

        if ($registration->status !== 'Menunggu') {
            return response()->json([
                'status' => 'error',
                'message' => "Pendaftaran tidak dapat diverifikasi karena berstatus '{$registration->status}'.",
            ], 422);
        }

        $result = DB::transaction(function () use ($registration, $nurse) {
            // Hitung nomor urut antrean untuk dokter & tanggal yang sama
            $lastQueueCount = Registration::where('doctor_id', $registration->doctor_id)
                ->whereDate('registration_date', $registration->registration_date)
                ->whereNotNull('queue_number')
                ->count();

            // Format nomor antrean (misal: A-001, A-002)
            $nextNumber = $lastQueueCount + 1;
            $queueCode = 'A-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Update data pendaftaran
            $registration->update([
                'queue_number' => $queueCode,
                'status' => 'Terkonfirmasi',
                'verified_by_nurse_id' => $nurse->nurse_id,
            ]);

            return $registration;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Verifikasi berhasil. Nomor antrean telah diterbitkan.',
            'data' => $result->load(['patient', 'doctor.specialization', 'poli', 'nurse']),
        ]);
    }
}
