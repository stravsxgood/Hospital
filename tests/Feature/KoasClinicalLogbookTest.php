<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ClinicalLogbook;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

function createKoasTestEnvironment(): array
{
    $spec = Specialization::create([
        'code_specialization' => 'SPEC-SURG',
        'name_specialization' => 'Spesialis Bedah Umum',
    ]);

    $doctorUser = User::factory()->create(['role' => 'doctor', 'name' => 'dr. Bedah Spesialis, Sp.B']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Bedah Spesialis, Sp.B',
        'sip_number' => 'SIP.BEDAH.001',
        'gender' => 'Laki-laki',
        'join_date' => '2020-01-01',
        'status' => 'aktif',
    ]);

    $koasUser = User::factory()->create(['role' => 'nurse', 'name' => 'Dokter Muda Ahmad (Koas)']);
    $koas = Nurse::create([
        'user_id' => $koasUser->id,
        'name' => 'Dokter Muda Ahmad',
        'registration_number' => 'NIM-FK-2022001',
        'type' => 'koas',
        'gender' => 'Laki-laki',
        'institute' => 'Fakultas Kedokteran Negeri',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient', 'name' => 'Pasien Bedah']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Bedah',
        'resident_n' => '3201999988880001',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-01-01',
        'number_phone' => '08199998888',
        'registration_date' => Carbon::today()->toDateString(),
    ]);

    return compact('doctorUser', 'doctor', 'koasUser', 'koas', 'patientUser', 'patient');
}

test('koas intern can save a draft clinical logbook and later submit it to DPJP', function () {
    $env = createKoasTestEnvironment();

    // 1. Simpan Draft Logbook
    $response = $this->actingAs($env['koasUser'])
        ->postJson('/koas/logbook', [
            'patient_id' => $env['patient']->patient_id,
            'doctor_id' => $env['doctor']->doctor_id,
            'activity_type' => 'anamnesis',
            'case_title' => 'Anamnesis Appendisitis Akut',
            'clinical_findings' => 'Nyeri perut kanan bawah (McBurney Sign positif), mual, demam.',
            'learning_reflection' => 'Pentingnya membedakan nyeri appendisitis dengan kolik renal.',
            'submit_now' => false,
        ]);

    $response->assertCreated();
    $logbookId = $response->json('data.clinical_logbook_id');
    expect($response->json('data.status'))->toBe('draft');

    // 2. Submit ke DPJP
    $submitResponse = $this->actingAs($env['koasUser'])
        ->postJson("/koas/logbook/{$logbookId}/submit");

    $submitResponse->assertOk();
    expect($submitResponse->json('data.status'))->toBe('submitted');

    // 3. Pastikan tidak bisa diedit setelah submit
    $editResponse = $this->actingAs($env['koasUser'])
        ->putJson("/koas/logbook/{$logbookId}", [
            'case_title' => 'Judul Berubah Setelah Submit',
            'clinical_findings' => 'Temuan Baru',
            'learning_reflection' => 'Refleksi Baru',
            'activity_type' => 'anamnesis',
        ]);

    $editResponse->assertStatus(422);
});

test('dpjp doctor can inspect assigned koas logbooks and approve with score and feedback', function () {
    $env = createKoasTestEnvironment();

    $logbook = ClinicalLogbook::create([
        'nurse_id' => $env['koas']->nurse_id,
        'patient_id' => $env['patient']->patient_id,
        'doctor_id' => $env['doctor']->doctor_id,
        'activity_type' => 'procedure_assistance',
        'case_title' => 'Asistensi Debridement Luka Bakar Derajat II',
        'clinical_findings' => 'Luka bakar 15% TBSA pada ekstremitas superior.',
        'procedure_performed' => 'Pembersihan jaringan nekrotik, irigasi NaCl 0.9%, dressing steril.',
        'learning_reflection' => 'Teknik aseptik ketat mutlak untuk mencegah sepsis pada pasien luka bakar.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    // DPJP Review and Sign-off (Approved)
    $response = $this->actingAs($env['doctorUser'])
        ->postJson("/doctor/supervision/{$logbook->clinical_logbook_id}/review", [
            'status' => 'approved',
            'supervisor_feedback' => 'Penalaran klinis sangat baik, pertahankan teknik aseptik yang tepat.',
            'score' => 92,
        ]);

    $response->assertOk();

    $logbook->refresh();
    expect($logbook->status)->toBe('approved');
    expect($logbook->score)->toBe(92);
    expect($logbook->supervisor_feedback)->toContain('Penalaran klinis sangat baik');
    expect($logbook->reviewed_at)->not->toBeNull();
});

test('dpjp doctor can request revision on inadequate koas logbook', function () {
    $env = createKoasTestEnvironment();

    $logbook = ClinicalLogbook::create([
        'nurse_id' => $env['koas']->nurse_id,
        'patient_id' => $env['patient']->patient_id,
        'doctor_id' => $env['doctor']->doctor_id,
        'activity_type' => 'case_discussion',
        'case_title' => 'Diskusi Hernia Inguinalis',
        'clinical_findings' => 'Benjolan di lipat paha.',
        'learning_reflection' => 'Kurang membaca literatur anatomi kanalis inguinalis.',
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    // DPJP Request Revision
    $response = $this->actingAs($env['doctorUser'])
        ->postJson("/doctor/supervision/{$logbook->clinical_logbook_id}/review", [
            'status' => 'revision_needed',
            'supervisor_feedback' => 'Mohon tambahkan diferensiasi Hernia Inguinalis Lateralis vs Medialis dan manuver Zieman.',
            'score' => 65,
        ]);

    $response->assertOk();

    $logbook->refresh();
    expect($logbook->status)->toBe('revision_needed');
    expect($logbook->score)->toBe(65);
});
