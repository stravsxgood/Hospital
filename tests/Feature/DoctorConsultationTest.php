<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Prescription;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;

function createDoctorConsultationContext(): array
{
    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-TEST'],
        ['name_specialization' => 'Spesialis Penyakit Dalam (Sp.PD)', 'description' => 'Test Spec']
    );

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'POLI-TEST'],
        ['name_poli' => 'Poli Penyakit Dalam', 'location' => 'Lantai 2', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'RM-TEST'],
        ['name_room' => 'Ruang Periksa 201', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 2]
    );

    $doctorUser = User::factory()->create([
        'name' => 'dr. Hendra Pratama, Sp.PD',
        'email' => 'drhendra'.uniqid().'@test.com',
        'role' => 'doctor',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Hendra Pratama, Sp.PD',
        'sip_number' => 'SIP-PD-'.uniqid(),
        'gender' => 'Laki-laki',
        'number_phone' => '081299990001',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00:00',
        'end_time' => '14:00:00',
        'quota_day' => 25,
        'status' => 'Aktif',
    ]);

    $patientUser = User::factory()->create([
        'name' => 'Siti Nurhaliza',
        'email' => 'siti'.uniqid().'@test.com',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => (string) rand(3201000000000000, 3201999999999999),
        'name' => 'Siti Nurhaliza',
        'gender' => 'Perempuan',
        'birthday_date' => '1992-07-20',
        'number_phone' => '081388880002',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'POLI-TEST-001',
        'complaint' => 'Nyeri ulu hati dan mual sejak 2 hari',
        'status' => 'in_progress',
    ]);

    $medParacetamol = Medicine::create([
        'code_medicine' => 'MED-TEST-PCT-'.uniqid(),
        'name_medicine' => 'Paracetamol 500mg Test',
        'type' => 'Tablet',
        'stock' => 50,
        'unit' => 'Strip',
        'price' => 10000.00,
    ]);

    $medAntasida = Medicine::create([
        'code_medicine' => 'MED-TEST-ANT-'.uniqid(),
        'name_medicine' => 'Antasida Doen Test',
        'type' => 'Sirup',
        'stock' => 20,
        'unit' => 'Botol',
        'price' => 15000.00,
    ]);

    return compact(
        'spec',
        'poli',
        'room',
        'doctorUser',
        'doctor',
        'schedule',
        'patientUser',
        'patient',
        'appointment',
        'medParacetamol',
        'medAntasida'
    );
}

test('doctor can store SOAP EMR notes and vital signs successfully', function () {
    $ctx = createDoctorConsultationContext();

    $payload = [
        'patient_id' => $ctx['patient']->patient_id,
        'reservation_id' => $ctx['appointment']->appointment_id,
        'subjective' => 'Pasien mengeluhkan perih di epigastrium dan kembung.',
        'objective' => [
            'systolic' => 120,
            'diastolic' => 80,
            'pulse' => 78,
            'temperature' => 36.6,
            'respiratory_rate' => 18,
            'weight' => 55,
            'height' => 160,
            'bmi' => 21.5,
            'oxygen_saturation' => 99,
        ],
        'assessment' => 'Dispepsia Fungsional / Gastritis Akut (K29.7)',
        'plan' => 'Diet rendah asam dan pedas, makan teratur porsi kecil bertahap.',
        'physical_check' => 'Nyeri tekan epigastrium (+), bising usus normal.',
    ];

    $response = $this->actingAs($ctx['doctorUser'])
        ->postJson('/doctor/consultations', $payload);

    $response->assertCreated();
    $response->assertJson([
        'status' => true,
        'message' => 'Pemeriksaan medis SOAP & Resep elektronik berhasil disimpan.',
    ]);

    // Assert MedicalRecord in database
    $this->assertDatabaseHas('medical_record', [
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_id' => $ctx['doctor']->doctor_id,
        'reservation_id' => $ctx['appointment']->appointment_id,
        'subjective' => 'Pasien mengeluhkan perih di epigastrium dan kembung.',
        'assessment' => 'Dispepsia Fungsional / Gastritis Akut (K29.7)',
    ]);

    // Assert Appointment status updated to completed
    $ctx['appointment']->refresh();
    expect($ctx['appointment']->status)->toBe('completed');
});

test('doctor can create dynamic E-Prescription and automatically deduct pharmacy medicine stock', function () {
    $ctx = createDoctorConsultationContext();

    $initialPctStock = $ctx['medParacetamol']->stock; // 50
    $initialAntStock = $ctx['medAntasida']->stock; // 20

    $prescribedPctQty = 3;
    $prescribedAntQty = 2;

    $payload = [
        'patient_id' => $ctx['patient']->patient_id,
        'reservation_id' => $ctx['appointment']->appointment_id,
        'subjective' => 'Demam dan mual.',
        'objective' => [
            'systolic' => 110,
            'diastolic' => 70,
            'pulse' => 84,
            'temperature' => 38.2,
            'weight' => 55,
            'height' => 160,
        ],
        'assessment' => 'Febris Akut susp. Viral Infection + Dispepsia',
        'plan' => 'Antipiretik, antasida, dan rehidrasi oral.',
        'prescription_notes' => 'Minum antasida 30 menit sebelum makan.',
        'prescription_items' => [
            [
                'medicine_id' => $ctx['medParacetamol']->medicine_id,
                'quantity' => $prescribedPctQty,
                'dosage' => '3 x 1 Tablet Sehari',
                'instructions' => 'Bila demam / sesudah makan',
                'notes' => 'Maksimal 3 tablet per hari',
            ],
            [
                'medicine_id' => $ctx['medAntasida']->medicine_id,
                'quantity' => $prescribedAntQty,
                'dosage' => '3 x 1 Sendok Takar',
                'instructions' => 'Sebelum makan',
                'notes' => 'Kocok dahulu',
            ],
        ],
    ];

    $response = $this->actingAs($ctx['doctorUser'])
        ->postJson('/doctor/consultations', $payload);

    $response->assertCreated();

    // Verify Prescription record created
    $prescription = Prescription::whereHas('medicalRecord', function ($q) use ($ctx) {
        $q->where('patient_id', $ctx['patient']->patient_id);
    })->first();

    expect($prescription)->not->toBeNull();
    expect($prescription->status)->toBe('menunggu');
    expect($prescription->prescription_number)->toStartWith('RX-');
    expect($prescription->items)->toHaveCount(2);

    // Verify Medicine Stock was decremented
    $ctx['medParacetamol']->refresh();
    $ctx['medAntasida']->refresh();

    expect($ctx['medParacetamol']->stock)->toBe($initialPctStock - $prescribedPctQty); // 50 - 3 = 47
    expect($ctx['medAntasida']->stock)->toBe($initialAntStock - $prescribedAntQty); // 20 - 2 = 18
});

test('prescription creation fails with validation error if requested quantity exceeds available stock', function () {
    $ctx = createDoctorConsultationContext();
    $initialStock = $ctx['medParacetamol']->stock; // 50

    $payload = [
        'patient_id' => $ctx['patient']->patient_id,
        'reservation_id' => $ctx['appointment']->appointment_id,
        'subjective' => 'Sakit kepala hebat.',
        'objective' => ['systolic' => 120, 'diastolic' => 80],
        'assessment' => 'Tension Type Headache',
        'plan' => 'Analgetik dan istirahat.',
        'prescription_items' => [
            [
                'medicine_id' => $ctx['medParacetamol']->medicine_id,
                'quantity' => 100, // Exceeds available stock (50)
                'dosage' => '3 x 1 Tablet',
                'instructions' => 'Sesudah makan',
            ],
        ],
    ];

    $response = $this->actingAs($ctx['doctorUser'])
        ->postJson('/doctor/consultations', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['prescription_items']);

    // Ensure stock remained intact (no partial deduction)
    $ctx['medParacetamol']->refresh();
    expect($ctx['medParacetamol']->stock)->toBe($initialStock);

    // Ensure no medical record or prescription was stored
    expect(MedicalRecord::where('patient_id', $ctx['patient']->patient_id)->count())->toBe(0);
});

test('doctor can retrieve patient past clinical history with eager-loaded relations', function () {
    $ctx = createDoctorConsultationContext();

    // 1. Create a prior medical record with prescription
    $record = MedicalRecord::create([
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_id' => $ctx['doctor']->doctor_id,
        'reservation_id' => $ctx['appointment']->appointment_id,
        'subjective' => 'Riwayat keluhan sebelumnya.',
        'objective' => ['systolic' => 120, 'diastolic' => 80, 'temperature' => 36.5],
        'assessment' => 'Faringitis Akut (J02.9)',
        'plan' => 'Gargle hangat dan antibiotik.',
    ]);

    Prescription::create([
        'medical_record_id' => $record->medical_record_id,
        'prescription_number' => 'RX-HIST-001',
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($ctx['doctorUser'])
        ->getJson("/doctor/patients/{$ctx['patient']->patient_id}/history");

    $response->assertOk();
    $response->assertJsonStructure([
        'status',
        'patient' => ['patient_id', 'name', 'resident_n'],
        'data' => [
            '*' => [
                'medical_record_id',
                'subjective',
                'objective',
                'assessment',
                'plan',
                'doctor' => ['doctor_id', 'name', 'specialization'],
            ],
        ],
    ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['assessment'])->toBe('Faringitis Akut (J02.9)');
});

test('non-doctor user is forbidden from storing medical consultation records', function () {
    $ctx = createDoctorConsultationContext();

    $payload = [
        'patient_id' => $ctx['patient']->patient_id,
        'subjective' => 'Keluhan',
        'objective' => ['systolic' => 120],
        'assessment' => 'Diagnosa',
        'plan' => 'Plan',
    ];

    $response = $this->actingAs($ctx['patientUser'])
        ->postJson('/doctor/consultations', $payload);

    $response->assertForbidden();
});

test('doctor can fetch available medicines catalog with live stocks and search filtering', function () {
    $ctx = createDoctorConsultationContext();

    $response = $this->actingAs($ctx['doctorUser'])
        ->getJson('/doctor/medicines?search=Paracetamol');

    $response->assertOk();
    $response->assertJson([
        'status' => true,
    ]);

    $items = $response->json('data');
    expect(count($items))->toBeGreaterThanOrEqual(1);
    expect($items[0]['name_medicine'])->toContain('Paracetamol');
});
