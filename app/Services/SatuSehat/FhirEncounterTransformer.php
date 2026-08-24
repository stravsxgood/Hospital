<?php

declare(strict_types=1);

namespace App\Services\SatuSehat;

use App\Models\MedicalRecord;

/**
 * Service FhirEncounterTransformer
 *
 * Mengonversi data rekam medis internal SIMRS (MedicalRecord, Pasien, Dokter, Diagnosis ICD-10, & Tanda Vital)
 * menjadi payload standar HL7 / FHIR R4 (Fast Healthcare Interoperability Resources)
 * yang kompatibel dengan integrasi Platform SatuSehat Kementerian Kesehatan Republik Indonesia.
 */
class FhirEncounterTransformer
{
    /**
     * Ubah Rekam Medis (EMR) menjadi Dokumen FHIR Bundle lengkap (Encounter + Condition + Observations).
     *
     * @return array<string, mixed>
     */
    public function toFhirBundle(MedicalRecord $record): array
    {
        $record->loadMissing([
            'patient',
            'doctor.specialization',
            'reservation.doctorSchedule.poli',
            'reservation.doctorSchedule.room',
            'prescription.items.medicine',
        ]);

        $patient = $record->patient;
        $doctor = $record->doctor;
        $schedule = $record->reservation?->doctorSchedule;
        $poli = $schedule?->poli;
        $room = $schedule?->room;

        $patientId = $patient ? (string) $patient->patient_id : '0';
        $patientName = $patient ? $patient->name : 'Unknown Patient';
        $patientNik = $patient ? $patient->resident_n : '';

        $doctorId = $doctor ? (string) $doctor->doctor_id : '0';
        $doctorName = $doctor ? $doctor->name : 'Dokter Pemeriksa';

        $poliName = $poli ? ($poli->name_poli ?? $poli->name ?? 'Poliklinik') : 'Poliklinik Rawat Jalan';
        $roomName = $room ? ($room->name_room ?? $room->name ?? 'Ruang Pemeriksa') : 'Ruang Konsultasi';

        $encounterUuid = "urn:uuid:encounter-{$record->medical_record_id}";
        $createdAtIso = $record->created_at ? $record->created_at->toIso8601String() : now()->toIso8601String();

        $bundleEntries = [];

        // 1. FHIR Resource: Encounter (Kunjungan Konsultasi Rawat Jalan)
        $bundleEntries[] = [
            'fullUrl' => $encounterUuid,
            'resource' => [
                'resourceType' => 'Encounter',
                'id' => "enc-{$record->medical_record_id}",
                'identifier' => [
                    [
                        'system' => 'http://sys-ids.kemkes.go.id/encounter/hospital-population',
                        'value' => "ENC-{$record->medical_record_id}",
                    ],
                ],
                'status' => 'finished',
                'class' => [
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                    'code' => 'AMB',
                    'display' => 'ambulatory',
                ],
                'subject' => [
                    'reference' => "Patient/{$patientId}",
                    'display' => $patientName,
                    'identifier' => [
                        'system' => 'https://fhir.kemkes.go.id/id/nik',
                        'value' => $patientNik,
                    ],
                ],
                'participant' => [
                    [
                        'type' => [
                            [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                        'code' => 'ATND',
                                        'display' => 'attender',
                                    ],
                                ],
                            ],
                        ],
                        'individual' => [
                            'reference' => "Practitioner/{$doctorId}",
                            'display' => $doctorName,
                        ],
                    ],
                ],
                'period' => [
                    'start' => $createdAtIso,
                    'end' => $createdAtIso,
                ],
                'location' => [
                    [
                        'location' => [
                            'reference' => "Location/poli-{$schedule?->poli_id}",
                            'display' => "{$poliName} - {$roomName}",
                        ],
                    ],
                ],
                'serviceProvider' => [
                    'reference' => 'Organization/hospital-population-simrs',
                    'display' => 'Hospital Population SIMRS',
                ],
            ],
            'request' => [
                'method' => 'POST',
                'url' => 'Encounter',
            ],
        ];

        // 2. FHIR Resource: Condition (Diagnosis Penyakit / ICD-10)
        $assessment = trim((string) $record->assessment);
        $icdCode = 'R69'; // Default un-specified diagnosis
        $icdDisplay = $assessment ?: 'Illness, unspecified';

        // Deteksi pola kode ICD-10 pada teks assessment (misal: "A09 - Diare Akut" atau "J00 Common Cold")
        if (preg_match('/^([A-Z][0-9]{2}(\.[0-9]+)?)\s*[-:]?\s*(.+)?/i', $assessment, $matches)) {
            $icdCode = strtoupper(trim($matches[1]));
            $icdDisplay = ! empty($matches[3]) ? trim($matches[3]) : $assessment;
        }

        $bundleEntries[] = [
            'fullUrl' => "urn:uuid:condition-{$record->medical_record_id}",
            'resource' => [
                'resourceType' => 'Condition',
                'id' => "cond-{$record->medical_record_id}",
                'clinicalStatus' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                            'code' => 'active',
                        ],
                    ],
                ],
                'verificationStatus' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                            'code' => 'confirmed',
                        ],
                    ],
                ],
                'category' => [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                'code' => 'encounter-diagnosis',
                                'display' => 'Encounter Diagnosis',
                            ],
                        ],
                    ],
                ],
                'code' => [
                    'coding' => [
                        [
                            'system' => 'http://hl7.org/fhir/sid/icd-10',
                            'code' => $icdCode,
                            'display' => $icdDisplay,
                        ],
                    ],
                    'text' => $assessment,
                ],
                'subject' => [
                    'reference' => "Patient/{$patientId}",
                    'display' => $patientName,
                ],
                'encounter' => [
                    'reference' => $encounterUuid,
                ],
                'recordedDate' => $createdAtIso,
            ],
            'request' => [
                'method' => 'POST',
                'url' => 'Condition',
            ],
        ];

        // 3. FHIR Resources: Observations (Tanda-Tanda Vital / LOINC Standards)
        $objective = is_array($record->objective) ? $record->objective : [];

        // Mapping parameter tanda vital ke kode LOINC standar
        $loincMappings = [
            'systolic' => [
                'code' => '8480-6',
                'display' => 'Systolic blood pressure',
                'unit' => 'mm[Hg]',
                'unit_label' => 'mmHg',
            ],
            'diastolic' => [
                'code' => '8462-4',
                'display' => 'Diastolic blood pressure',
                'unit' => 'mm[Hg]',
                'unit_label' => 'mmHg',
            ],
            'heart_rate' => [
                'code' => '8867-4',
                'display' => 'Heart rate',
                'unit' => '/min',
                'unit_label' => 'bpm',
            ],
            'pulse' => [
                'code' => '8867-4',
                'display' => 'Heart rate',
                'unit' => '/min',
                'unit_label' => 'bpm',
            ],
            'temperature' => [
                'code' => '8310-5',
                'display' => 'Body temperature',
                'unit' => 'Cel',
                'unit_label' => '°C',
            ],
            'respiratory_rate' => [
                'code' => '9279-1',
                'display' => 'Respiratory rate',
                'unit' => '/min',
                'unit_label' => 'x/menit',
            ],
            'spo2' => [
                'code' => '2708-6',
                'display' => 'Oxygen saturation in Arterial blood',
                'unit' => '%',
                'unit_label' => '%',
            ],
            'weight' => [
                'code' => '29463-7',
                'display' => 'Body weight',
                'unit' => 'kg',
                'unit_label' => 'kg',
            ],
            'height' => [
                'code' => '8302-2',
                'display' => 'Body height',
                'unit' => 'cm',
                'unit_label' => 'cm',
            ],
        ];

        // Handle blood pressure compound string jika format "120/80"
        if (! empty($objective['blood_pressure']) && is_string($objective['blood_pressure'])) {
            $parts = explode('/', $objective['blood_pressure']);
            if (isset($parts[0])) {
                $objective['systolic'] = trim($parts[0]);
            }
            if (isset($parts[1])) {
                $objective['diastolic'] = trim($parts[1]);
            }
        }

        foreach ($objective as $key => $val) {
            $normalizedKey = strtolower(str_replace([' ', '-'], '_', (string) $key));
            if (! isset($loincMappings[$normalizedKey]) || ! is_numeric($val)) {
                continue;
            }

            $meta = $loincMappings[$normalizedKey];
            $numVal = (float) $val;

            $bundleEntries[] = [
                'fullUrl' => "urn:uuid:observation-{$record->medical_record_id}-{$normalizedKey}",
                'resource' => [
                    'resourceType' => 'Observation',
                    'id' => "obs-{$record->medical_record_id}-{$normalizedKey}",
                    'status' => 'final',
                    'category' => [
                        [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                    'code' => 'vital-signs',
                                    'display' => 'Vital Signs',
                                ],
                            ],
                        ],
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://loinc.org',
                                'code' => $meta['code'],
                                'display' => $meta['display'],
                            ],
                        ],
                        'text' => $meta['display'],
                    ],
                    'subject' => [
                        'reference' => "Patient/{$patientId}",
                        'display' => $patientName,
                    ],
                    'encounter' => [
                        'reference' => $encounterUuid,
                    ],
                    'effectiveDateTime' => $createdAtIso,
                    'issued' => $createdAtIso,
                    'valueQuantity' => [
                        'value' => $numVal,
                        'unit' => $meta['unit_label'],
                        'system' => 'http://unitsofmeasure.org',
                        'code' => $meta['unit'],
                    ],
                ],
                'request' => [
                    'method' => 'POST',
                    'url' => 'Observation',
                ],
            ];
        }

        return [
            'resourceType' => 'Bundle',
            'id' => "bundle-satusehat-{$record->medical_record_id}",
            'type' => 'transaction',
            'entry' => $bundleEntries,
        ];
    }
}
