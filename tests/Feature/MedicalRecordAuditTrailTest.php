<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAuditLog;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('audit log service immutably logs medical record access and allows staff viewer', function () {
    $spec = Specialization::create([
        'code_specialization' => 'SPEC-PED',
        'name_specialization' => 'Spesialis Anak',
    ]);

    $doctorUser = User::factory()->create(['role' => 'doctor', 'name' => 'dr. Anak Spesialis']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Anak Spesialis',
        'sip_number' => 'SIP.ANAK.001',
        'gender' => 'Perempuan',
        'join_date' => '2020-01-01',
        'status' => 'aktif',
    ]);

    $nurseUser = User::factory()->create(['role' => 'nurse', 'name' => 'Ns. Ratna Staff']);
    $nurse = Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Ns. Ratna Staff',
        'registration_number' => 'STR-NURSE-002',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient', 'name' => 'Ananda Rifky']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Ananda Rifky',
        'resident_n' => '3201888877770001',
        'gender' => 'Laki-laki',
        'birthday_date' => '2018-03-20',
        'number_phone' => '08188887777',
        'registration_date' => Carbon::today()->toDateString(),
    ]);

    $medicalRecord = MedicalRecord::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'subjective' => 'Batuk berdahak 3 hari',
        'objective' => ['temperature' => 37.8, 'pulse' => 90],
        'assessment' => 'Bronkitis Akut',
        'plan' => 'Mukolitik dan banyak minum air hangat',
    ]);

    // 1. Audit Log: View EMR
    $logView = AuditLogService::logAccess(
        medicalRecordId: $medicalRecord->medical_record_id,
        action: 'view',
        payloadDiff: ['section' => 'history'],
        userId: $doctorUser->id
    );

    expect($logView->action)->toBe('view');
    expect($logView->medical_record_id)->toBe($medicalRecord->medical_record_id);

    // 2. Audit Log: Export PDF
    $logExport = AuditLogService::logAccess(
        medicalRecordId: $medicalRecord->medical_record_id,
        action: 'export_pdf',
        payloadDiff: ['document' => 'Resume Medis'],
        userId: $nurseUser->id
    );

    expect($logExport->action)->toBe('export_pdf');

    // 3. Verifikasi Staff dapat mengambil list audit log
    $response = $this->actingAs($nurseUser)
        ->getJson('/staff/audit-logs');

    $response->assertOk();
    expect($response->json('data.total'))->toBeGreaterThanOrEqual(2);
});
