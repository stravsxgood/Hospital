<?php

use App\Models\Doctor;
use App\Models\Icd10Diagnosis;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\SoapTemplate;
use App\Models\Specialization;
use App\Models\User;

function setupClinicalDoctorEnvironment(): array
{
    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-CLI'],
        ['name_specialization' => 'Spesialis Penyakit Dalam', 'description' => 'Internist']
    );

    $doctorUser = User::factory()->create([
        'name' => 'dr. Sarah Connor, Sp.PD',
        'email' => 'sarah.'.uniqid().'@hospital.test',
        'role' => 'doctor',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Sarah Connor, Sp.PD',
        'sip_number' => 'SIP-CLI-'.uniqid(),
        'gender' => 'Perempuan',
        'number_phone' => '081299887766',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $patientUser = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.'.uniqid().'@patient.test',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3171'.rand(100000000000, 999999999999),
        'name' => 'John Doe',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-05-15',
        'number_phone' => '081233445566',
        'address' => 'Jl. Merdeka No. 10',
        'registration_date' => now()->toDateString(),
    ]);

    return compact('doctorUser', 'doctor', 'patientUser', 'patient');
}

test('icd-10 search endpoint returns autocomplete matching items by code or name', function () {
    $env = setupClinicalDoctorEnvironment();

    Icd10Diagnosis::firstOrCreate(
        ['code' => 'J00'],
        [
            'name_id' => 'Nasofaringitis akut (common cold / selesma)',
            'name_en' => 'Acute nasopharyngitis',
            'is_common' => true,
        ]
    );

    $response = $this->actingAs($env['doctorUser'])
        ->getJson('/api/clinical/icd10?q=nasofaringitis');

    $response->assertOk()
        ->assertJson([
            'status' => true,
        ]);

    expect($response->json('data'))->not->toBeEmpty();
    expect($response->json('data.0.code'))->toBe('J00');
});

test('doctor can retrieve system and own soap templates', function () {
    $env = setupClinicalDoctorEnvironment();

    SoapTemplate::create([
        'doctor_id' => null, // Global
        'template_name' => 'ISPA Dewasa Standar',
        'subjective_template' => 'Keluhan batuk pilek demam',
        'assessment_template' => 'ISPA / Acute Upper Respiratory Infection (J06.9)',
        'plan_template' => 'Simptomatik, hidrasi, istirahat',
    ]);

    SoapTemplate::create([
        'doctor_id' => $env['doctor']->doctor_id, // Own doctor
        'template_name' => 'Gastritis Kronik Khusus dr. Sarah',
        'subjective_template' => 'Nyeri ulu hati, mual',
        'assessment_template' => 'Gastritis kronis (K29.5)',
        'plan_template' => 'PPI 2x1, antasida 3x1 ac',
    ]);

    $response = $this->actingAs($env['doctorUser'])
        ->getJson('/api/clinical/soap-templates');

    $response->assertOk()
        ->assertJson([
            'status' => true,
        ]);

    $templates = collect($response->json('data'));
    expect($templates->pluck('template_name'))->toContain('ISPA Dewasa Standar');
    expect($templates->pluck('template_name'))->toContain('Gastritis Kronik Khusus dr. Sarah');
});

test('doctor can save custom soap template', function () {
    $env = setupClinicalDoctorEnvironment();

    $response = $this->actingAs($env['doctorUser'])
        ->postJson('/api/clinical/soap-templates', [
            'template_name' => 'Template Hipertensi Stage 1',
            'subjective_template' => 'Pasien mengeluhkan pusing tengkuk kaku',
            'objective_template' => ['systolic' => 140, 'diastolic' => 90],
            'assessment_template' => 'Hipertensi Primer / Esensial (I10)',
            'plan_template' => 'Amlodipine 5mg 1x1 malam, diet rendah garam',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'Template SOAP berhasil disimpan.',
        ]);

    $this->assertDatabaseHas('soap_template', [
        'template_name' => 'Template Hipertensi Stage 1',
        'doctor_id' => $env['doctor']->doctor_id,
    ]);
});

test('clinical safety interceptor catches allergy warnings and drug-drug interactions', function () {
    $env = setupClinicalDoctorEnvironment();

    // Pasien memiliki riwayat alergi Amoxicillin / Penicillin
    PatientAllergy::create([
        'patient_id' => $env['patient']->patient_id,
        'allergen_type' => 'medicine',
        'allergen_name' => 'Amoxicillin',
        'severity' => 'severe',
        'reaction' => 'Ruam kulit kemerahan dan sesak napas',
    ]);

    $medAmox = Medicine::create([
        'code_medicine' => 'MED-AMOX-01',
        'name_medicine' => 'Amoxicillin 500mg Kapsul',
        'type' => 'Antibiotik',
        'stock' => 100,
        'price' => 5000,
        'unit' => 'Kapsul',
    ]);

    $medCipro = Medicine::create([
        'code_medicine' => 'MED-CIPRO-01',
        'name_medicine' => 'Ciprofloxacin 500mg Tablet',
        'type' => 'Antibiotik',
        'stock' => 100,
        'price' => 8000,
        'unit' => 'Tablet',
    ]);

    $medAntacid = Medicine::create([
        'code_medicine' => 'MED-ANTAC-01',
        'name_medicine' => 'Antasida Doen Tablet Kunyah',
        'type' => 'Antasida',
        'stock' => 100,
        'price' => 3000,
        'unit' => 'Tablet',
    ]);

    // Test 1: Deteksi Alergi Obat
    $responseAllergy = $this->actingAs($env['doctorUser'])
        ->postJson('/api/clinical/check-safety', [
            'patient_id' => $env['patient']->patient_id,
            'medicines' => [$medAmox->medicine_id],
        ]);

    $responseAllergy->assertOk();
    expect($responseAllergy->json('data.has_warnings'))->toBeTrue();
    expect($responseAllergy->json('data.allergy_alerts'))->not->toBeEmpty();
    expect($responseAllergy->json('data.allergy_alerts.0.allergen_name'))->toBe('Amoxicillin');

    // Test 2: Deteksi Interaksi Antar Obat (Ciprofloxacin + Antasida)
    $responseInteraction = $this->actingAs($env['doctorUser'])
        ->postJson('/api/clinical/check-safety', [
            'patient_id' => $env['patient']->patient_id,
            'medicines' => [$medCipro->medicine_id, $medAntacid->medicine_id],
        ]);

    $responseInteraction->assertOk();
    expect($responseInteraction->json('data.has_warnings'))->toBeTrue();
    expect($responseInteraction->json('data.interaction_alerts'))->not->toBeEmpty();
    expect($responseInteraction->json('data.interaction_alerts.0.severity'))->toBe('moderate');
});
