<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckClinicalSafetyRequest;
use App\Http\Requests\StoreSoapTemplateRequest;
use App\Models\Icd10Diagnosis;
use App\Models\SoapTemplate;
use App\Services\ClinicalSafetyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller ClinicalAssistantController
 *
 * Endpoint pembantu klinis dokter:
 * - Autocomplete pencarian kode ICD-10 WHO
 * - Pengambilan & penyimpanan preset SOAP Templates
 * - Pengecekan riwayat alergi obat & interaksi obat resep
 */
class ClinicalAssistantController extends Controller
{
    public function __construct(
        private readonly ClinicalSafetyService $clinicalSafetyService
    ) {}

    /**
     * Autocomplete pencarian diagnosis ICD-10
     */
    public function searchIcd10(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Icd10Diagnosis::query();

        if ($q !== '') {
            $query->search($q);
        } else {
            $query->common();
        }

        $results = $query->orderBy('code', 'asc')
            ->limit(20)
            ->get(['icd10_diagnosis_id', 'code', 'name_id', 'name_en', 'is_common']);

        return response()->json([
            'status' => true,
            'message' => 'ICD-10 diagnoses retrieved.',
            'data' => $results,
        ]);
    }

    /**
     * Ambil template SOAP untuk dokter yang sedang login (termasuk template default sistem)
     */
    public function getSoapTemplates(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctorId = $user?->doctor?->doctor_id;

        $templates = SoapTemplate::query()
            ->forDoctor($doctorId)
            ->orderBy('doctor_id', 'desc') // Template khusus dokter tampil duluan
            ->orderBy('template_name', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'SOAP templates retrieved.',
            'data' => $templates,
        ]);
    }

    /**
     * Simpan template SOAP khusus buatan dokter
     */
    public function storeSoapTemplate(StoreSoapTemplateRequest $request): JsonResponse
    {
        $user = $request->user();
        $doctorId = $user?->doctor?->doctor_id;

        $validated = $request->validated();
        $validated['doctor_id'] = $doctorId;

        $template = SoapTemplate::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Template SOAP berhasil disimpan.',
            'data' => $template,
        ], 201);
    }

    /**
     * Cek keamanan klinis peresepan obat (alergi & interaksi obat)
     */
    public function checkSafety(CheckClinicalSafetyRequest $request): JsonResponse
    {
        $patientId = $request->validated('patient_id');
        $medicines = $request->validated('medicines');

        $evaluation = $this->clinicalSafetyService->evaluatePrescriptionSafety(
            $patientId ? (int) $patientId : null,
            $medicines
        );

        return response()->json([
            'status' => true,
            'message' => 'Clinical safety evaluated successfully.',
            'data' => $evaluation,
        ]);
    }
}
