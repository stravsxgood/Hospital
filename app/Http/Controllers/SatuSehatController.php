<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Services\SatuSehat\FhirEncounterTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller SatuSehatController
 *
 * Menyediakan endpoint integrasi interoperabilitas SatuSehat Kemenkes (HL7 / FHIR R4).
 */
class SatuSehatController extends Controller
{
    public function __construct(
        private readonly FhirEncounterTransformer $transformer
    ) {}

    /**
     * Mengambil payload FHIR Bundle standar untuk satu Rekam Medis (EMR).
     */
    public function getFhirBundle(int $medical_record_id, Request $request): JsonResponse
    {
        $record = MedicalRecord::with([
            'patient',
            'doctor.specialization',
            'reservation.doctorSchedule.poli',
            'reservation.doctorSchedule.room',
            'prescription.items.medicine',
        ])->findOrFail($medical_record_id);

        $bundle = $this->transformer->toFhirBundle($record);

        return response()->json([
            'status'  => true,
            'message' => 'FHIR R4 SatuSehat Bundle generated successfully.',
            'data'    => $bundle,
        ]);
    }
}
