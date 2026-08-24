<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Services\FEFODispensationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Class StaffActionController
 * 
 * Menangani aksi operasional meja depan (front-office check-in) dan
 * penyiapan obat farmasi (prescription fulfillment & FEFO batch stock deduction).
 * Audio queue calling diisolasi secara eksklusif hanya untuk Dokter.
 */
class StaffActionController extends Controller
{
    public function __construct(
        private readonly FEFODispensationService $fefoDispensationService
    ) {}
    /**
     * Konfirmasi kedatangan / Check-in pasien di Front-Office meja depan.
     * Mengubah status reservasi dari 'pending' (menunggu) menjadi 'confirmed' (dikonfirmasi/hadir),
     * sehingga dokter dapat melihat pasien siap di ruang periksa.
     *
     * @param int|string $id ID appointment / reservasi
     */
    public function confirmArrival(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $appointment = Appointment::with(['patient', 'doctorSchedule.doctor', 'doctorSchedule.poli'])
            ->findOrFail($id);

        if ($appointment->status === 'cancelled') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Janji temu pasien ini telah dibatalkan sebelumnya.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Janji temu pasien ini telah dibatalkan.');
        }

        if ($appointment->status !== 'pending') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Status kedatangan pasien sudah dikonfirmasi sebelumnya.',
                    'data'    => $appointment,
                ]);
            }
            return redirect()->back()->with('info', 'Kedatangan pasien sudah dikonfirmasi.');
        }

        $appointment->update([
            'status' => 'confirmed',
        ]);

        $patientName = $appointment->patient?->name ?? 'Pasien';
        $queueNumber = $appointment->queue_number ?? '-';
        $doctorName = $appointment->doctorSchedule?->doctor?->name ?? 'Dokter Spesialis';
        $poliName = $appointment->doctorSchedule?->poli?->name_poli ?? $appointment->doctorSchedule?->poli?->name ?? 'Poli';
        $doctorId = (int) ($appointment->doctorSchedule?->doctor_id ?? 0);

        // Siarkan event kedatangan pasien langsung ke konsol dokter DPJP via Reverb
        if ($doctorId > 0) {
            event(new \App\Events\PatientConfirmedEvent(
                reservationId: (int) $appointment->appointment_id,
                queueNumber: (string) $queueNumber,
                patientName: (string) $patientName,
                doctorId: $doctorId,
                status: 'confirmed',
                confirmedAt: now()->toIso8601String()
            ));
        }

        $successMessage = "Kedatangan pasien {$patientName} ({$queueNumber}) berhasil dikonfirmasi untuk {$poliName} ({$doctorName}). Pasien siap diperiksa.";

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => $successMessage,
                'data'    => $appointment->fresh(['patient', 'doctorSchedule.doctor', 'doctorSchedule.poli']),
            ]);
        }

        return redirect()->back()->with('success', $successMessage);
    }

    /**
     * Mengubah status resep farmasi menjadi 'diproses' (sedang diracik).
     * Dibatasi hanya untuk Staf / Perawat Tetap (Pekerja).
     */
    public function processPrescription(Request $request, int $id): JsonResponse|RedirectResponse
    {
        Gate::authorize('access-pekerja-only');

        $prescription = Prescription::with(['medicalRecord.patient', 'items.medicine'])
            ->findOrFail($id);

        if ($prescription->status === 'selesai') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Resep ini sudah selesai diracik sebelumnya.',
                ], 422);
            }
            return redirect()->back()->with('info', 'Resep ini sudah selesai diracik.');
        }

        $prescription->update([
            'status' => 'diproses',
        ]);

        $message = "Resep #{$prescription->prescription_number} sedang dalam proses penyiapan oleh staf farmasi.";

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => $message,
                'data'    => $prescription->fresh(),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Menyelesaikan penyiapan resep obat & mengurangi stok inventori farmasi secara atomik.
     * Dibatasi hanya untuk Staf / Perawat Tetap (Pekerja).
     */
    public function completePrescription(Request $request, int $id): JsonResponse|RedirectResponse
    {
        Gate::authorize('access-pekerja-only');

        $prescription = Prescription::with(['medicalRecord.patient', 'items.medicine'])
            ->findOrFail($id);

        if ($prescription->status === 'selesai') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Resep ini sudah ditandai selesai sebelumnya.',
                    'data'    => $prescription,
                ]);
            }
            return redirect()->back()->with('info', 'Resep ini sudah ditandai selesai.');
        }

        try {
            DB::transaction(function () use ($prescription) {
                // 1. Kurangi stok obat menggunakan FEFO (First Expired First Out)
                foreach ($prescription->items as $item) {
                    if ($item->medicine_id && $item->quantity > 0) {
                        $medicine = Medicine::where('medicine_id', $item->medicine_id)
                            ->lockForUpdate()
                            ->first();

                        if ($medicine) {
                            $this->fefoDispensationService->deductMedicine($medicine, (int) $item->quantity);
                        }
                    }
                }

                // 2. Update status resep ke selesai
                $prescription->update([
                    'status' => 'selesai',
                ]);
            });

            $message = "Resep #{$prescription->prescription_number} selesai diracik. Stok FEFO obat telah dipotong otomatis.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => true,
                    'message' => $message,
                    'data'    => $prescription->fresh(['medicalRecord.patient', 'items.medicine']),
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Gagal menyelesaikan resep: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal memproses resep: ' . $e->getMessage());
        }
    }
}
