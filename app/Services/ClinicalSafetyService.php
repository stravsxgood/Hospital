<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Medicine;
use App\Models\PatientAllergy;

/**
 * Service ClinicalSafetyService
 *
 * Engine pemeriksa keamanan klinis peresepan obat:
 * 1. Pengecekan riwayat alergi obat pasien (Drug-Allergy Cross Check)
 * 2. Pengecekan interaksi obat-obat berisiko (Drug-Drug Interaction Checker)
 */
class ClinicalSafetyService
{
    /**
     * Database pasangan interaksi obat berisiko klinis (Drug-Drug Interaction Rules)
     * Format: [kategori_1 => [kategori_2 => ['severity' => ..., 'message' => ...]]]
     */
    private const KNOWN_INTERACTIONS = [
        [
            'drug_a' => ['ciprofloxacin', 'levofloxacin', 'antibiotik quinolone'],
            'drug_b' => ['antasida', 'antacid', 'magnesium hidroksida', 'aluminium hidroksida', 'sucralfate'],
            'severity' => 'moderate',
            'mechanism' => 'Khelasi & Penurunan Absorpsi',
            'advice' => 'Antasida menurunkan penyerapan Quinolone hingga 90%. Beri jeda minimal 2 jam sebelum atau 4 jam sesudah minum antasida.',
        ],
        [
            'drug_a' => ['warfarin', 'antikoagulan'],
            'drug_b' => ['aspirin', 'ibuprofen', 'asam mefenamat', 'ketorolac', 'meloxicam', 'natrium diklofenak', 'nsaid'],
            'severity' => 'severe',
            'mechanism' => 'Peningkatan Risiko Perdarahan Mayor',
            'advice' => 'Kombinasi Warfarin dan NSAID meningkatkan risiko perdarahan gastrointestinal hebat secara drastis.',
        ],
        [
            'drug_a' => ['simvastatin', 'atorvastatin'],
            'drug_b' => ['amlodipine', 'diltiazem'],
            'severity' => 'moderate',
            'mechanism' => 'Peningkatan Kadar Statin dalam Darah',
            'advice' => 'Amlodipine meningkatkan konsentrasi serum Simvastatin. Batasi dosis Simvastatin maksimal 20 mg/hari untuk mencegah risiko rhabdomyolisis.',
        ],
        [
            'drug_a' => ['paracetamol', 'acetaminophen'],
            'drug_b' => ['tramadol', 'paracetamol + tramadol'],
            'severity' => 'mild',
            'mechanism' => 'Duplikasi Komponen Parasetamol',
            'advice' => 'Perhatikan total asupan harian Parasetamol tidak melebihi 4000 mg/24 jam untuk mencegah hepatotoksisitas.',
        ],
        [
            'drug_a' => ['amoxicillin', 'ampicillin', 'penicillin'],
            'drug_b' => ['allopurinol'],
            'severity' => 'moderate',
            'mechanism' => 'Peningkatan Risiko Ruam Kulit Alergi',
            'advice' => 'Kombinasi Allopurinol dan Aminopenicillin meningkatkan insiden ruam kulit (skin rash) secara signifikan.',
        ],
    ];

    /**
     * Periksa keamanan klinis dari daftar obat yang akan diresepkan untuk pasien tertentu.
     *
     * @param  int|null  $patientId  ID Pasien yang sedang diperiksa
     * @param  array  $medicines  List of medicines (bisa berupa model Medicine, ID obat, atau nama obat)
     * @return array Hasil evaluasi klinis alergi & interaksi
     */
    public function evaluatePrescriptionSafety(?int $patientId, array $medicines): array
    {
        $allergyAlerts = [];
        $interactionAlerts = [];

        // 1. Ambil daftar nama obat dan ID obat yang diresepkan
        $prescribedList = [];
        foreach ($medicines as $item) {
            if ($item instanceof Medicine) {
                $prescribedList[] = [
                    'id' => $item->medicine_id,
                    'name' => strtolower(trim((string) ($item->name_medicine ?? $item->name ?? ''))),
                    'code' => (string) ($item->code_medicine ?? $item->code ?? ''),
                ];
            } elseif (is_array($item)) {
                $prescribedList[] = [
                    'id' => $item['medicine_id'] ?? null,
                    'name' => strtolower(trim((string) ($item['name_medicine'] ?? $item['name'] ?? ''))),
                    'code' => (string) ($item['code_medicine'] ?? $item['code'] ?? ''),
                ];
            } elseif (is_numeric($item)) {
                $med = Medicine::find($item);
                if ($med) {
                    $prescribedList[] = [
                        'id' => $med->medicine_id,
                        'name' => strtolower(trim((string) ($med->name_medicine ?? $med->name ?? ''))),
                        'code' => (string) ($med->code_medicine ?? $med->code ?? ''),
                    ];
                }
            }
        }

        // 2. Cek Riwayat Alergi Pasien
        if ($patientId) {
            $patientAllergies = PatientAllergy::query()
                ->where('patient_id', $patientId)
                ->get();

            foreach ($patientAllergies as $allergy) {
                $allergenLower = strtolower(trim($allergy->allergen_name));

                foreach ($prescribedList as $med) {
                    if (
                        str_contains($med['name'], $allergenLower) ||
                        str_contains($allergenLower, $med['name'])
                    ) {
                        $allergyAlerts[] = [
                            'allergen_name' => $allergy->allergen_name,
                            'medicine_name' => $med['name'],
                            'severity' => $allergy->severity,
                            'reaction' => $allergy->reaction,
                            'message' => "Pasien memiliki riwayat alergi terhadap '{$allergy->allergen_name}' ({$allergy->severity}). Reaksi: {$allergy->reaction}.",
                        ];
                    }
                }
            }
        }

        // 3. Cek Interaksi Antar Obat (Drug-Drug Interaction)
        $count = count($prescribedList);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $medA = $prescribedList[$i]['name'];
                $medB = $prescribedList[$j]['name'];

                foreach (self::KNOWN_INTERACTIONS as $rule) {
                    $matchA1 = $this->matchesRule($medA, $rule['drug_a']);
                    $matchB1 = $this->matchesRule($medB, $rule['drug_b']);

                    $matchA2 = $this->matchesRule($medA, $rule['drug_b']);
                    $matchB2 = $this->matchesRule($medB, $rule['drug_a']);

                    if (($matchA1 && $matchB1) || ($matchA2 && $matchB2)) {
                        $interactionAlerts[] = [
                            'drug_1' => $prescribedList[$i]['name'],
                            'drug_2' => $prescribedList[$j]['name'],
                            'severity' => $rule['severity'],
                            'mechanism' => $rule['mechanism'],
                            'advice' => $rule['advice'],
                        ];
                    }
                }
            }
        }

        $hasWarnings = count($allergyAlerts) > 0 || count($interactionAlerts) > 0;
        $hasSevere = collect($allergyAlerts)->contains('severity', 'severe') ||
            collect($interactionAlerts)->contains('severity', 'severe');

        return [
            'has_warnings' => $hasWarnings,
            'has_severe' => $hasSevere,
            'allergy_alerts' => $allergyAlerts,
            'interaction_alerts' => $interactionAlerts,
        ];
    }

    /**
     * Memeriksa apakah string nama obat mengandung salah satu kata kunci aturan
     */
    private function matchesRule(string $medName, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($medName, strtolower($kw))) {
                return true;
            }
        }

        return false;
    }
}
