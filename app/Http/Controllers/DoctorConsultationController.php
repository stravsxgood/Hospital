<?php

namespace App\Http\Controllers;

use App\Events\PrescriptionCreatedEvent;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Controller DoctorConsultationController
 * Mengelola alur konsultasi klinis dokter:
 * 1. Penyimpanan Rekam Medis Elektronik (EMR SOAP Notes & Vital Signs)
 * 2. E-Prescription Builder (Pembuatan resep & pengurangan stok otomatis)
 * 3. Timeline Riwayat Klinis Pasien (Patient Medical History)
 */
class DoctorConsultationController extends Controller
{
    /**
     * Menyimpan hasil konsultasi dokter: Rekam Medis (SOAP) dan Resep Obat (E-Prescription).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $doctor = $user?->doctor;

        if (! $doctor) {
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hanya akun dokter terdaftar yang dapat mengisi rekam medis dan resep obat.',
                ], 403);
            }
            abort(403, 'Akses khusus dokter.');
        }

        // 1. Validasi Input SOAP dan Item Resep
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patient,patient_id'],
            'reservation_id' => ['nullable', 'integer'],
            'subjective' => ['required', 'string'],
            'objective' => ['required', 'array'],
            'objective.systolic' => ['nullable', 'numeric', 'min:40', 'max:300'],
            'objective.diastolic' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'objective.pulse' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'objective.temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'objective.respiratory_rate' => ['nullable', 'numeric', 'min:5', 'max:80'],
            'objective.weight' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'objective.height' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'objective.oxygen_saturation' => ['nullable', 'numeric', 'min:50', 'max:100'],
            'assessment' => ['required', 'string'],
            'plan' => ['required', 'string'],
            'physical_check' => ['nullable', 'string'],
            'prescription_items' => ['nullable', 'array'],
            'prescription_items.*.medicine_id' => ['required_with:prescription_items', 'integer', 'exists:medicine,medicine_id'],
            'prescription_items.*.quantity' => ['required_with:prescription_items', 'integer', 'min:1', 'max:500'],
            'prescription_items.*.dosage' => ['required_with:prescription_items', 'string', 'max:100'],
            'prescription_items.*.instructions' => ['required_with:prescription_items', 'string', 'max:255'],
            'prescription_items.*.notes' => ['nullable', 'string', 'max:255'],
            'prescription_notes' => ['nullable', 'string'],
        ], [
            'patient_id.required' => 'ID Pasien wajib disertakan.',
            'subjective.required' => 'Keluhan subjektif (Subjective) wajib diisi.',
            'objective.required' => 'Data objektif / tanda vital (Objective) wajib diisi.',
            'assessment.required' => 'Diagnosis kerja (Assessment) wajib diisi.',
            'plan.required' => 'Rencana terapi & edukasi (Plan) wajib diisi.',
        ]);

        // 2. Transaksi Database Terpadu
        DB::beginTransaction();

        try {
            // A. Simpan Rekam Medis (SOAP Notes)
            $medicalRecord = MedicalRecord::create([
                'patient_id' => (int) $validated['patient_id'],
                'doctor_id' => (int) $doctor->doctor_id,
                'reservation_id' => ! empty($validated['reservation_id']) ? (int) $validated['reservation_id'] : null,
                'subjective' => $validated['subjective'],
                'objective' => $validated['objective'],
                'assessment' => $validated['assessment'],
                'plan' => $validated['plan'],
                'physical_check' => $validated['physical_check'] ?? null,
            ]);

            // B. Jika ada resep obat yang dibuat
            $prescription = null;
            $prescriptionItems = $validated['prescription_items'] ?? [];

            if (! empty($prescriptionItems)) {
                // Generate nomor resep unik: RX-YYYYMMDD-XXXX
                $datePrefix = now()->format('Ymd');
                $randomSuffix = strtoupper(Str::random(4));
                $prescriptionNumber = "RX-{$datePrefix}-{$randomSuffix}";

                // Pastikan nomor resep unik
                while (Prescription::where('prescription_number', $prescriptionNumber)->exists()) {
                    $randomSuffix = strtoupper(Str::random(4));
                    $prescriptionNumber = "RX-{$datePrefix}-{$randomSuffix}";
                }

                $prescription = Prescription::create([
                    'medical_record_id' => $medicalRecord->medical_record_id,
                    'prescription_number' => $prescriptionNumber,
                    'status' => 'menunggu',
                    'notes' => $validated['prescription_notes'] ?? null,
                ]);

                // Loop setiap item obat, cek stok dengan pessimistic lock & kurangi stok
                foreach ($prescriptionItems as $item) {
                    $medicine = Medicine::lockForUpdate()->findOrFail($item['medicine_id']);

                    $requestedQty = (int) $item['quantity'];

                    if ($medicine->stock < $requestedQty) {
                        throw ValidationException::withMessages([
                            'prescription_items' => "Stok obat '{$medicine->name_medicine}' tidak mencukupi. Tersedia: {$medicine->stock} {$medicine->unit}, diminta: {$requestedQty}.",
                        ]);
                    }

                    // Kurangi stok obat
                    $medicine->decrement('stock', $requestedQty);

                    // Buat rincian item resep
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->prescription_id,
                        'medicine_id' => $medicine->medicine_id,
                        'quantity' => $requestedQty,
                        'dosage' => $item['dosage'],
                        'instructions' => $item['instructions'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            // C. Perbarui status appointment / reservasi antrean menjadi 'completed'
            $appointment = null;
            if (! empty($validated['reservation_id'])) {
                $appointment = Appointment::with(['doctorSchedule.poli'])->find($validated['reservation_id']);
                if ($appointment) {
                    $appointment->update([
                        'status' => 'completed',
                    ]);
                }
            }

            // D. Catat Audit Trail UU PDP atas pembuatan Rekam Medis baru
            AuditLogService::logAccess(
                medicalRecordId: (int) $medicalRecord->medical_record_id,
                action: 'create',
                payloadDiff: [
                    'assessment' => $medicalRecord->assessment,
                    'has_prescription' => $prescription !== null,
                    'prescription_number' => $prescription?->prescription_number,
                ]
            );

            // E. Siarkan event notifikasi resep instan ke antrean farmasi via Reverb
            if ($prescription) {
                $patient = Patient::find($medicalRecord->patient_id);
                $poliName = $appointment?->doctorSchedule?->poli?->name ?? 'Poliklinik';
                event(new PrescriptionCreatedEvent(
                    prescriptionId: (int) $prescription->prescription_id,
                    prescriptionNumber: (string) $prescription->prescription_number,
                    patientName: (string) ($patient?->name ?? 'Pasien'),
                    doctorName: (string) ($doctor->name ?? 'Dokter Spesialis'),
                    poliName: (string) $poliName,
                    totalItems: count($prescriptionItems),
                    createdAt: now()->toIso8601String()
                ));
            }

            DB::commit();

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Pemeriksaan medis SOAP & Resep elektronik berhasil disimpan.',
                    'data' => [
                        'medical_record_id' => $medicalRecord->medical_record_id,
                        'prescription_id' => $prescription?->prescription_id,
                    ],
                ], 201);
            }

            return back()->with('success', 'Pemeriksaan medis SOAP & Resep elektronik berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($e instanceof ValidationException) {
                throw $e;
            }

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menyimpan rekam medis: '.$e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Gagal menyimpan rekam medis: '.$e->getMessage()]);
        }
    }

    /**
     * Mengambil riwayat rekam medis dan klinis masa lalu seorang pasien (Patient Medical History).
     */
    public function getPatientHistory(int $patient_id): JsonResponse
    {
        $patient = Patient::findOrFail($patient_id);

        $history = MedicalRecord::with([
            'doctor.specialization',
            'prescription.items.medicine',
            'reservation.doctorSchedule.poli',
        ])
            ->where('patient_id', $patient->patient_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Catat Audit Trail UU PDP untuk setiap rekam medis yang dibuka/dilihat oleh dokter
        foreach ($history as $record) {
            AuditLogService::logAccess(
                medicalRecordId: (int) $record->medical_record_id,
                action: 'view',
                payloadDiff: ['screen' => 'ConsultationModal - PatientHistory']
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Riwayat rekam medis pasien berhasil diambil.',
            'patient' => [
                'patient_id' => $patient->patient_id,
                'name' => $patient->name,
                'resident_n' => $patient->resident_n,
                'gender' => $patient->gender,
                'birthday_date' => $patient->birthday_date,
                'number_phone' => $patient->number_phone,
                'registration_date' => $patient->registration_date,
            ],
            'data' => $history,
        ]);
    }

    /**
     * Mengambil katalog obat untuk pencarian / dropdown resep elektronik.
     */
    public function getMedicines(Request $request): JsonResponse
    {
        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_medicine', 'like', "%{$search}%")
                    ->orWhere('code_medicine', 'like', "%{$search}%");
            });
        }

        $medicines = $query->orderBy('name_medicine', 'asc')->get();

        return response()->json([
            'status' => true,
            'data' => $medicines,
        ]);
    }
}
