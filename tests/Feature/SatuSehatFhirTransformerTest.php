<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use App\Services\SatuSehat\FhirEncounterTransformer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('fhir encounter transformer produces valid FHIR R4 Bundle with encounter, condition, and observations', function () {
    $spec = Specialization::create([
        'code_specialization' => 'SPEC-CARD',
        'name_specialization' => 'Spesialis Jantung & Pembuluh Darah',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'POLI-CARD',
        'name_poli' => 'Poli Jantung',
        'location' => 'Lantai 2 Sayap Timur',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-CARD1',
        'name_room' => 'Ruang Kardio 1',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);

    $doctorUser = User::factory()->create(['role' => 'doctor', 'name' => 'dr. Jantung, Sp.JP']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Jantung, Sp.JP',
        'sip_number' => 'SIP.JANTUNG.001',
        'gender' => 'Laki-laki',
        'join_date' => '2020-01-01',
        'status' => 'aktif',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient', 'name' => 'Ibu Siti Aminah']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Ibu Siti Aminah',
        'resident_n' => '3201111122220001',
        'gender' => 'Perempuan',
        'birthday_date' => '1970-08-17',
        'number_phone' => '08111112222',
        'registration_date' => Carbon::today()->toDateString(),
    ]);

    $medicalRecord = MedicalRecord::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'subjective' => 'Nyeri dada kiri menjalar ke punggung saat beraktivitas',
        'objective' => [
            'systolic' => 140,
            'diastolic' => 90,
            'heart_rate' => 88,
            'temperature' => 36.6,
            'spo2' => 98,
        ],
        'assessment' => 'I20.9 - Angina Pectoris, Unspecified',
        'plan' => 'Nitrat sublingual, EKG berkala, dan rujukan treadmill test',
    ]);

    $transformer = app(FhirEncounterTransformer::class);
    $bundle = $transformer->toFhirBundle($medicalRecord);

    expect($bundle['resourceType'])->toBe('Bundle');
    expect($bundle['type'])->toBe('transaction');
    expect($bundle['entry'])->not->toBeEmpty();

    // 1. Verifikasi Resource Encounter
    $encounterEntry = collect($bundle['entry'])->firstWhere('resource.resourceType', 'Encounter');
    expect($encounterEntry)->not->toBeNull();
    expect($encounterEntry['resource']['status'])->toBe('finished');
    expect($encounterEntry['resource']['subject']['display'])->toBe('Ibu Siti Aminah');

    // 2. Verifikasi Resource Condition (ICD-10)
    $conditionEntry = collect($bundle['entry'])->firstWhere('resource.resourceType', 'Condition');
    expect($conditionEntry)->not->toBeNull();
    expect($conditionEntry['resource']['code']['coding'][0]['code'])->toBe('I20.9');

    // 3. Verifikasi Resource Observation (Vital Signs)
    $observations = collect($bundle['entry'])->where('resource.resourceType', 'Observation');
    expect($observations->count())->toBeGreaterThanOrEqual(4);

    // 4. Verifikasi Endpoint API SatuSehat
    $response = $this->actingAs($doctorUser)
        ->getJson("/api/satusehat/records/{$medicalRecord->medical_record_id}/fhir-bundle");

    $response->assertOk();
    expect($response->json('status'))->toBeTrue();
    expect($response->json('data.resourceType'))->toBe('Bundle');
});
