<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Icd10Diagnosis;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\SoapTemplate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HospitalClinicalMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed ICD-10 Diagnoses
        $diagnoses = [
            [
                'code'      => 'J00',
                'name_id'   => 'Nasofaringitis akut (common cold / batuk pilek)',
                'name_en'   => 'Acute nasopharyngitis [common cold]',
                'is_common' => true,
            ],
            [
                'code'      => 'J06.9',
                'name_id'   => 'Infeksi saluran pernapasan atas akut (ISPA)',
                'name_en'   => 'Acute upper respiratory infection, unspecified',
                'is_common' => true,
            ],
            [
                'code'      => 'K30',
                'name_id'   => 'Dispepsia / Maag / Nyeri ulu hati fungsional',
                'name_en'   => 'Dyspepsia',
                'is_common' => true,
            ],
            [
                'code'      => 'K29.7',
                'name_id'   => 'Gastritis tidak spesifik',
                'name_en'   => 'Gastritis, unspecified',
                'is_common' => true,
            ],
            [
                'code'      => 'I10',
                'name_id'   => 'Hipertensi esensial (primer)',
                'name_en'   => 'Essential (primary) hypertension',
                'is_common' => true,
            ],
            [
                'code'      => 'E11.9',
                'name_id'   => 'Diabetes melitus tipe 2 tanpa komplikasi',
                'name_en'   => 'Type 2 diabetes mellitus without complications',
                'is_common' => true,
            ],
            [
                'code'      => 'A09',
                'name_id'   => 'Gastroenteritis dan kolitis infeksius (diare akut)',
                'name_en'   => 'Infectious gastroenteritis and colitis, unspecified',
                'is_common' => true,
            ],
            [
                'code'      => 'M54.5',
                'name_id'   => 'Nyeri punggung bawah (Low back pain)',
                'name_en'   => 'Low back pain',
                'is_common' => true,
            ],
            [
                'code'      => 'R50.9',
                'name_id'   => 'Demam, tidak spesifik',
                'name_en'   => 'Fever, unspecified',
                'is_common' => true,
            ],
            [
                'code'      => 'R51',
                'name_id'   => 'Sakit kepala / Cephalgia',
                'name_en'   => 'Headache',
                'is_common' => true,
            ],
            [
                'code'      => 'L20',
                'name_id'   => 'Dermatitis atopik / Eksim',
                'name_en'   => 'Atopic dermatitis',
                'is_common' => false,
            ],
            [
                'code'      => 'Z00.0',
                'name_id'   => 'Pemeriksaan kesehatan umum (General medical examination)',
                'name_en'   => 'General medical examination',
                'is_common' => true,
            ],
        ];

        foreach ($diagnoses as $diag) {
            Icd10Diagnosis::firstOrCreate(['code' => $diag['code']], $diag);
        }

        // 2. Seed Standard SOAP Templates
        $soapTemplates = [
            [
                'template_name'        => 'ISPA / Batuk Pilek Akut',
                'subjective_template'  => "Pasien mengeluhkan demam ringan disertai batuk berdahak/kering, pilek hidung tersumbat, dan tenggorokan gatal sejak 2-3 hari yang lalu. Tidak ada sesak napas berat.",
                'objective_template'   => [
                    'systolic'         => 120,
                    'diastolic'        => 80,
                    'pulse'            => 84,
                    'temperature'      => 37.8,
                    'respiratory_rate' => 18,
                    'notes'            => 'Faring hiperemis (+), tonsil T1-T1 tenang, rhonki (-/-), wheezing (-/-).'
                ],
                'assessment_template'  => 'J06.9 - Infeksi Saluran Pernapasan Atas (ISPA) Akut',
                'plan_template'        => "1. Istirahat cukup & hidrasi air putih hangat minimal 2L/hari.\n2. Edukasi etika batuk dan pemakaian masker.\n3. Terapi simtomatik antipiretik dan mukolitik.\n4. Kontrol ulang jika demam tinggi > 3 hari atau timbul sesak.",
            ],
            [
                'template_name'        => 'Dispepsia / Gastritis Akut',
                'subjective_template'  => "Nyeri ulu hati (epigastrium) terasa perih dan panas, mual (+), kembung (begah), riwayat makan tidak teratur dan sering konsumsi kopi/makanan pedas.",
                'objective_template'   => [
                    'systolic'         => 118,
                    'diastolic'        => 78,
                    'pulse'            => 80,
                    'temperature'      => 36.6,
                    'respiratory_rate' => 16,
                    'notes'            => 'Abdomen supel, nyeri tekan epigastrium (+), bising usus normal (8x/m), defans muskular (-).'
                ],
                'assessment_template'  => 'K30 - Dispepsia / Sindrom Gastritis Akut',
                'plan_template'        => "1. Hindari makanan pedas, asam, bersantan, dan kafein/kopi.\n2. Pola makan porsi kecil tapi sering (small frequent meals).\n3. Antasida/PPI sebelum makan.\n4. Edukasi manajemen stres.",
            ],
            [
                'template_name'        => 'Hipertensi Primer Terkontrol',
                'subjective_template'  => "Kontrol rutin tekanan darah bulanan. Keluhan leher terasa kaku kadang-kadang, tidak ada nyeri dada khas angina, tidak ada pandangan kabur.",
                'objective_template'   => [
                    'systolic'         => 138,
                    'diastolic'        => 88,
                    'pulse'            => 76,
                    'temperature'      => 36.5,
                    'respiratory_rate' => 18,
                    'notes'            => 'Suara jantung S1 S2 reguler murni, murmur (-), gallop (-), edema tungkai (-/-).'
                ],
                'assessment_template'  => 'I10 - Hipertensi Esensial (Primer)',
                'plan_template'        => "1. Diet rendah garam (DASH diet).\n2. Aktivitas fisik aerobik teratur 30 menit/hari.\n3. Lanjutkan terapi antihipertensi harian secara teratur.\n4. Kontrol tekanan darah dan fungsi ginjal berkala.",
            ],
        ];

        foreach ($soapTemplates as $template) {
            SoapTemplate::firstOrCreate(
                ['template_name' => $template['template_name']],
                array_merge($template, ['doctor_id' => null])
            );
        }

        // 3. Seed Patient Allergies (Contoh data uji keselamatan klinis)
        $patients = Patient::limit(5)->get();
        if ($patients->isNotEmpty()) {
            $patient = $patients->first();
            PatientAllergy::firstOrCreate(
                [
                    'patient_id'    => $patient->patient_id,
                    'allergen_name' => 'Amoxicillin',
                ],
                [
                    'allergen_type' => 'medicine',
                    'severity'      => 'severe',
                    'reaction'      => 'Ruam merah kulit seluruh tubuh, biduran, bibir bengkak (angioedema)',
                ]
            );

            PatientAllergy::firstOrCreate(
                [
                    'patient_id'    => $patient->patient_id,
                    'allergen_name' => 'Penicillin',
                ],
                [
                    'allergen_type' => 'medicine',
                    'severity'      => 'severe',
                    'reaction'      => 'Gatal hebat & urtikaria',
                ]
            );
        }

        // 4. Seed Medicine Batches (FEFO Engine Data)
        $medicines = Medicine::all();
        foreach ($medicines as $med) {
            // Batch 1: Expiring soon (dalam 20 hari)
            MedicineBatch::firstOrCreate(
                [
                    'medicine_id'  => $med->medicine_id,
                    'batch_number' => 'BATCH-' . strtoupper(substr(md5($med->code . '1'), 0, 6)),
                ],
                [
                    'expiry_date'    => Carbon::today()->addDays(20),
                    'stock_quantity' => min(15, max(5, intval($med->stock / 3))),
                    'purchase_price' => $med->price * 0.7,
                ]
            );

            // Batch 2: Safe expiry (1 tahun ke depan)
            MedicineBatch::firstOrCreate(
                [
                    'medicine_id'  => $med->medicine_id,
                    'batch_number' => 'BATCH-' . strtoupper(substr(md5($med->code . '2'), 0, 6)),
                ],
                [
                    'expiry_date'    => Carbon::today()->addMonths(14),
                    'stock_quantity' => max(10, intval($med->stock * 2 / 3)),
                    'purchase_price' => $med->price * 0.7,
                ]
            );
        }
    }
}
