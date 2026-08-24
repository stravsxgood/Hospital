<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReviewClinicalLogbookRequest;
use App\Models\ClinicalLogbook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller DoctorSupervisionController
 *
 * Portal Supervisi Klinis bagi Dokter DPJP Pembimbing untuk memeriksa, mengevaluasi,
 * memberikan umpan balik (feedback), memberi nilai (scoring), dan menyetujui (Dual Sign-off)
 * logbook kasus klinis yang diajukan oleh Mahasiswa Koas.
 */
class DoctorSupervisionController extends Controller
{
    /**
     * Tampilkan daftar logbook yang perlu disupervisi oleh dokter DPJP ini.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $doctor = $request->user()?->doctor;
        if (! $doctor) {
            abort(403, 'Akses terbatas untuk akun Dokter DPJP.');
        }

        $query = ClinicalLogbook::with([
            'nurse.user',
            'patient',
            'medicalRecord.doctor.specialization',
            'medicalRecord.prescription.items.medicine',
        ])
            ->where('doctor_id', $doctor->doctor_id);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->query('activity_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('case_title', 'like', "%{$search}%")
                    ->orWhere('clinical_findings', 'like', "%{$search}%")
                    ->orWhereHas('nurse', function ($nq) use ($search) {
                        $nq->where('name', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logbooks = $query->orderByRaw("
                CASE 
                    WHEN status = 'submitted' THEN 1
                    WHEN status = 'revision_needed' THEN 2
                    WHEN status = 'draft' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'data' => $logbooks,
            ]);
        }

        return Inertia::render('doctor/Supervision/Index', [
            'logbooks' => $logbooks,
            'filters' => [
                'status' => $request->query('status', ''),
                'activity_type' => $request->query('activity_type', ''),
                'search' => $request->query('search', ''),
            ],
            'stats' => [
                'total_assigned' => ClinicalLogbook::where('doctor_id', $doctor->doctor_id)->count(),
                'pending_review' => ClinicalLogbook::where('doctor_id', $doctor->doctor_id)->where('status', 'submitted')->count(),
                'approved' => ClinicalLogbook::where('doctor_id', $doctor->doctor_id)->where('status', 'approved')->count(),
                'revision_needed' => ClinicalLogbook::where('doctor_id', $doctor->doctor_id)->where('status', 'revision_needed')->count(),
            ],
        ]);
    }

    /**
     * Detail logbook untuk inspeksi mendalam (side-by-side dengan rekam medis resmi).
     */
    public function show(int $id): JsonResponse
    {
        $doctor = request()->user()?->doctor;
        if (! $doctor) {
            abort(403, 'Akses terbatas untuk akun Dokter DPJP.');
        }

        $logbook = ClinicalLogbook::with([
            'nurse.user',
            'patient',
            'medicalRecord.doctor.specialization',
            'medicalRecord.prescription.items.medicine',
            'medicalRecord.reservation.doctorSchedule.poli',
        ])
            ->where('doctor_id', $doctor->doctor_id)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $logbook,
        ]);
    }

    /**
     * Submit supervisi: Memberikan evaluasi, skor, dan keputusan Dual Sign-off (Approved / Revision Needed).
     */
    public function review(ReviewClinicalLogbookRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $doctor = $request->user()->doctor;
        $logbook = ClinicalLogbook::where('doctor_id', $doctor->doctor_id)->findOrFail($id);

        $validated = $request->validated();

        $logbook->update([
            'status' => $validated['status'],
            'supervisor_feedback' => $validated['supervisor_feedback'],
            'score' => $validated['score'] ?? $logbook->score,
            'reviewed_at' => now(),
        ]);

        $statusLabel = $validated['status'] === 'approved' ? 'disetujui (Approved)' : 'diminta revisi';

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Supervisi logbook klinis berhasil disimpan. Status: {$statusLabel}.",
                'data' => $logbook->fresh(['nurse', 'patient']),
            ]);
        }

        return redirect()->back()->with('success', "Supervisi logbook berhasil disimpan. Status: {$statusLabel}.");
    }
}
