<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicalLogbookRequest;
use App\Http\Requests\UpdateClinicalLogbookRequest;
use App\Models\ClinicalLogbook;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller KoasLogbookController
 *
 * Mengelola pencatatan kasus klinis digital, prosedur tindakan, dan refleksi pembelajaran
 * untuk Mahasiswa Kedokteran / Dokter Muda (Koas) dengan alur Dual Sign-off DPJP.
 */
class KoasLogbookController extends Controller
{
    /**
     * Tampilkan daftar logbook klinis milik mahasiswa koas.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $nurse = $request->user()?->nurse;
        if (! $nurse) {
            abort(403, 'Akses terbatas untuk akun perawat/mahasiswa koas.');
        }

        $query = ClinicalLogbook::with([
            'patient',
            'doctor.specialization',
            'medicalRecord',
        ])
            ->where('nurse_id', $nurse->nurse_id);

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
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logbooks = $query->orderBy('created_at', 'desc')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'data'   => $logbooks,
            ]);
        }

        // Data master untuk modal tambah kasus
        $doctors = Doctor::with('specialization')->where('status', 'Aktif')->orderBy('name', 'asc')->get();
        $patients = Patient::select(['patient_id', 'name', 'resident_n', 'gender', 'birthday_date'])
            ->orderBy('name', 'asc')
            ->limit(100)
            ->get();

        return Inertia::render('koas/Logbook/Index', [
            'logbooks' => $logbooks,
            'doctors'  => $doctors,
            'patients' => $patients,
            'filters'  => [
                'status'        => $request->query('status', ''),
                'activity_type' => $request->query('activity_type', ''),
                'search'        => $request->query('search', ''),
            ],
            'stats' => [
                'total'            => ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->count(),
                'draft'            => ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->where('status', 'draft')->count(),
                'submitted'        => ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->where('status', 'submitted')->count(),
                'approved'         => ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->where('status', 'approved')->count(),
                'revision_needed'  => ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->where('status', 'revision_needed')->count(),
            ],
        ]);
    }

    /**
     * Simpan entri logbook klinis baru (Draft atau langsung Submit ke DPJP).
     */
    public function store(StoreClinicalLogbookRequest $request): JsonResponse|RedirectResponse
    {
        $nurse = $request->user()->nurse;
        $validated = $request->validated();

        $status = ! empty($validated['submit_now']) ? 'submitted' : 'draft';
        $submittedAt = $status === 'submitted' ? now() : null;

        $logbook = DB::transaction(function () use ($nurse, $validated, $status, $submittedAt) {
            return ClinicalLogbook::create([
                'nurse_id'            => (int) $nurse->nurse_id,
                'patient_id'          => (int) $validated['patient_id'],
                'doctor_id'           => (int) $validated['doctor_id'],
                'medical_record_id'   => ! empty($validated['medical_record_id']) ? (int) $validated['medical_record_id'] : null,
                'activity_type'       => $validated['activity_type'],
                'case_title'          => $validated['case_title'],
                'clinical_findings'   => $validated['clinical_findings'],
                'procedure_performed' => $validated['procedure_performed'] ?? null,
                'learning_reflection' => $validated['learning_reflection'],
                'status'              => $status,
                'submitted_at'        => $submittedAt,
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => $status === 'submitted' ? 'Logbook klinis berhasil diajukan ke DPJP.' : 'Draft logbook klinis berhasil disimpan.',
                'data'    => $logbook->load(['patient', 'doctor.specialization', 'medicalRecord']),
            ], 201);
        }

        return redirect()->back()->with('success', $status === 'submitted' ? 'Logbook klinis berhasil diajukan ke DPJP.' : 'Draft logbook berhasil disimpan.');
    }

    /**
     * Perbarui entri logbook (hanya jika masih berstatus draft atau revision_needed).
     */
    public function update(UpdateClinicalLogbookRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $nurse = $request->user()->nurse;
        $logbook = ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->findOrFail($id);

        if (in_array($logbook->status, ['approved', 'submitted'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Logbook yang sudah diajukan atau disetujui tidak dapat diubah.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Logbook yang sudah diajukan tidak dapat diubah.');
        }

        $validated = $request->validated();
        $status = ! empty($validated['submit_now']) ? 'submitted' : $logbook->status;
        $submittedAt = $status === 'submitted' ? now() : $logbook->submitted_at;

        $logbook->update([
            'patient_id'          => $validated['patient_id'] ?? $logbook->patient_id,
            'doctor_id'           => $validated['doctor_id'] ?? $logbook->doctor_id,
            'medical_record_id'   => array_key_exists('medical_record_id', $validated) ? $validated['medical_record_id'] : $logbook->medical_record_id,
            'activity_type'       => $validated['activity_type'] ?? $logbook->activity_type,
            'case_title'          => $validated['case_title'] ?? $logbook->case_title,
            'clinical_findings'   => $validated['clinical_findings'] ?? $logbook->clinical_findings,
            'procedure_performed' => array_key_exists('procedure_performed', $validated) ? $validated['procedure_performed'] : $logbook->procedure_performed,
            'learning_reflection' => $validated['learning_reflection'] ?? $logbook->learning_reflection,
            'status'              => $status,
            'submitted_at'        => $submittedAt,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Logbook klinis berhasil diperbarui.',
                'data'    => $logbook->fresh(['patient', 'doctor.specialization', 'medicalRecord']),
            ]);
        }

        return redirect()->back()->with('success', 'Logbook klinis berhasil diperbarui.');
    }

    /**
     * Ajukan draft logbook ke DPJP untuk ditinjau.
     */
    public function submitForReview(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $nurse = $request->user()->nurse;
        $logbook = ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->findOrFail($id);

        if ($logbook->status === 'approved') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Logbook ini sudah disetujui sebelumnya.',
                ], 422);
            }
            return redirect()->back()->with('info', 'Logbook ini sudah disetujui.');
        }

        $logbook->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Logbook klinis berhasil diajukan ke Dokter DPJP Pembimbing.',
                'data'    => $logbook->fresh(['patient', 'doctor.specialization']),
            ]);
        }

        return redirect()->back()->with('success', 'Logbook klinis berhasil diajukan ke Dokter DPJP Pembimbing.');
    }

    /**
     * Hapus draft logbook klinis.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $nurse = $request->user()->nurse;
        $logbook = ClinicalLogbook::where('nurse_id', $nurse->nurse_id)->findOrFail($id);

        if ($logbook->status === 'approved') {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Logbook yang sudah disetujui tidak dapat dihapus.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Logbook yang sudah disetujui tidak dapat dihapus.');
        }

        $logbook->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Draft logbook klinis berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Draft logbook klinis berhasil dihapus.');
    }
}
