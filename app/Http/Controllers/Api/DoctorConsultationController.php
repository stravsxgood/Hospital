<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Payment;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorConsultationController extends Controller
{
    /**
     * Menampilkan antrean pasien dokter hari ini yang siap diperiksa
     */
    public function index(Request $request): JsonResponse
    {

        $doctor = $request->user()->doctor;

        if (! $doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil dokter tidak ditemukan.',
            ], 403);
        }

        $today = Carbon::today()->toDateString();

        $consultations = Registration::with(['patient', 'poli'])
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate('registration_date', $today)
            ->whereIn('status', ['Terkonfirmasi', 'Sedang Diperiksa', 'Selesai'])
            ->orderByRaw("
                CASE 
                    WHEN status = 'Sedang Diperiksa' THEN 1
                    WHEN status = 'Terkonfirmasi' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('queue_number', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $consultations,
        ]);
    }

    /**
     * Update status antrean (misal: memanggil pasien menjadi 'Sedang Diperiksa')
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Sedang Diperiksa,Batal',
        ]);

        $doctor = $request->user()->doctor;
        $registration = Registration::where('doctor_id', $doctor->doctor_id)->findOrFail($id);

        $registration->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => "Status antrean diubah menjadi {$request->status}.",
            'data' => $registration,
        ]);
    }

    /**
     * Simpan hasil pemeriksaan medis (Diagnosa & Resep) dan selesaikan sesi
     */
    public function storeInspection(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'treatment' => 'required|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctor = $request->user()->doctor;
        $registration = Registration::where('doctor_id', $doctor->doctor_id)->findOrFail($id);

        $result = DB::transaction(function () use ($validated, $registration, $doctor) {
            // 1. Simpan data rekam medis
            $inspection = Inspection::create([
                'registration_id' => $registration->registration_id,
                'doctor_id' => $doctor->doctor_id,
                'inspection_date' => now(),
                'diagnosis' => $validated['diagnosis'],
                'treatment' => $validated['treatment'],
                'prescription' => $validated['prescription'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 2. Tandai pendaftaran selesai
            $registration->update(['status' => 'Selesai']);

            // 3. Buat draf tagihan kasir otomatis
            Payment::create([
                'registration_id' => $registration->registration_id,
                'payment_date' => now(),
                'payment_total' => 150000, // Biaya standar konsultasi dasar
                'payment_method' => 'Tunai',
                'payment_status' => 'Belum Lunas',
            ]);

            return $inspection;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pemeriksaan selesai dan rekam medis berhasil disimpan.',
            'data' => $result,
        ], 201);
    }
}
