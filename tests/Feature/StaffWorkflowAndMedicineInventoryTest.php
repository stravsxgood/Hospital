<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;

function setupStaffWorkflowEnvironment(): array
{
    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-FAR'],
        ['name_specialization' => 'Spesialis Penyakit Dalam', 'description' => 'Internist']
    );

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'PL-INT'],
        ['name_poli' => 'Poli Penyakit Dalam', 'location' => 'Lantai 2', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'RM-201'],
        ['name_room' => 'Ruang Periksa 201', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 2]
    );

    $doctorUser = User::factory()->create([
        'name' => 'dr. Handoko, Sp.PD',
        'email' => 'handoko' . uniqid() . '@test.com',
        'role' => 'doctor',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Handoko, Sp.PD',
        'sip_number' => 'SIP-PD-' . uniqid(),
        'gender' => 'Laki-laki',
        'number_phone' => '081233445566',
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
        'quota_day' => 30,
        'status' => 'Aktif',
    ]);

    // Permanent Nurse (Pekerja / Tetap)
    $nurseTetapUser = User::factory()->create([
        'name' => 'Ns. Dewi Anggraini (Tetap)',
        'email' => 'dewitetap' . uniqid() . '@test.com',
        'role' => 'nurse',
    ]);

    $nurseTetap = Nurse::create([
        'user_id' => $nurseTetapUser->id,
        'name' => 'Ns. Dewi Anggraini (Tetap)',
        'registration_number' => 'STR-N-TTP-' . uniqid(),
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    // Koas / Intern Nurse (Magang)
    $nurseKoasUser = User::factory()->create([
        'name' => 'dr. Muda Kevin (Koas)',
        'email' => 'kevinkoas' . uniqid() . '@test.com',
        'role' => 'nurse',
    ]);

    $nurseKoas = Nurse::create([
        'user_id' => $nurseKoasUser->id,
        'name' => 'dr. Muda Kevin (Koas)',
        'registration_number' => 'STR-N-KAS-' . uniqid(),
        'type' => 'koas',
        'gender' => 'Laki-laki',
    ]);

    // Patient
    $patientUser = User::factory()->create([
        'name' => 'Pasien Hendra',
        'email' => 'hendra' . uniqid() . '@test.com',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '33080' . rand(10000000000, 99999999999),
        'name' => 'Pasien Hendra',
        'gender' => 'Laki-laki',
        'birthday_date' => '1992-07-20',
        'address' => 'Jl. Pahlawan No. 45',
        'number_phone' => '081399887766',
        'disease' => 'Keluhan Lambung',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    // Medicine
    $medicine = Medicine::create([
        'code_medicine' => 'MED-OMZ-' . rand(100, 999),
        'name_medicine' => 'Omeprazole 20 mg',
        'type' => 'Kapsul',
        'stock' => 50,
        'unit' => 'Strip',
        'price' => 25000.00,
    ]);

    return compact(
        'spec', 'poli', 'room', 'doctorUser', 'doctor', 'schedule',
        'nurseTetapUser', 'nurseTetap', 'nurseKoasUser', 'nurseKoas',
        'patientUser', 'patient', 'medicine'
    );
}

test('staff dashboard renders successfully with unified front-office and pharmacy metrics', function () {
    $ctx = setupStaffWorkflowEnvironment();

    // Create an appointment for today
    $appointment = Appointment::create([
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_schedule_id' => $ctx['schedule']->doctor_schedule_id,
        'appointment_date' => today()->toDateString(),
        'queue_number' => 'A-001',
        'complaint' => 'Nyeri ulu hati',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->get('/staff/dashboard');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('StaffDashboard')
            ->has('stats')
            ->has('todayQueue')
            ->has('clinicMatrix')
            ->has('pendingPrescriptions')
            ->has('criticalMedicines')
        );
});

test('front-office staff can confirm patient arrival check-in transitioning status from pending to confirmed', function () {
    $ctx = setupStaffWorkflowEnvironment();

    $appointment = Appointment::create([
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_schedule_id' => $ctx['schedule']->doctor_schedule_id,
        'appointment_date' => today()->toDateString(),
        'queue_number' => 'A-002',
        'complaint' => 'Pemeriksaan rutin',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->post("/staff/reservations/{$appointment->appointment_id}/confirm-arrival");

    $response->assertRedirect();
    expect($appointment->fresh()->status)->toBe('confirmed');
});

test('front-office arrival confirmation responds with clean JSON when requested via API/XHR', function () {
    $ctx = setupStaffWorkflowEnvironment();

    $appointment = Appointment::create([
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_schedule_id' => $ctx['schedule']->doctor_schedule_id,
        'appointment_date' => today()->toDateString(),
        'queue_number' => 'A-003',
        'complaint' => 'Demam tinggi',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/reservations/{$appointment->appointment_id}/confirm-arrival");

    $response->assertOk()
        ->assertJson([
            'status' => true,
        ]);

    expect($appointment->fresh()->status)->toBe('confirmed');
});

test('permanent staff can process and complete pharmacy prescription with atomic stock deduction', function () {
    $ctx = setupStaffWorkflowEnvironment();

    $appointment = Appointment::create([
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_schedule_id' => $ctx['schedule']->doctor_schedule_id,
        'appointment_date' => today()->toDateString(),
        'queue_number' => 'A-004',
        'complaint' => 'Gangguan pencernaan',
        'status' => 'in_progress',
    ]);

    $medRecord = MedicalRecord::create([
        'reservation_id' => $appointment->appointment_id,
        'patient_id' => $ctx['patient']->patient_id,
        'doctor_id' => $ctx['doctor']->doctor_id,
        'subjective' => 'Pasien mengeluh mual dan nyeri lambung',
        'objective' => ['blood_pressure' => '120/80', 'heart_rate' => '78', 'temperature' => '36.8'],
        'assessment' => 'Dyspepsia Syndrome',
        'plan' => 'Terapi PPI selama 5 hari',
    ]);

    $prescription = Prescription::create([
        'medical_record_id' => $medRecord->medical_record_id,
        'prescription_number' => 'RX-' . date('Ymd') . '-001',
        'status' => 'menunggu',
        'notes' => 'Diminum 30 menit sebelum makan',
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->prescription_id,
        'medicine_id' => $ctx['medicine']->medicine_id,
        'quantity' => 5,
        'dosage' => '1 x 1 sehari',
        'instructions' => 'Sebelum makan',
    ]);

    $initialStock = $ctx['medicine']->stock; // 50

    // 1. Process prescription (menunggu -> diproses)
    $processResp = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/prescriptions/{$prescription->prescription_id}/process");

    $processResp->assertOk();
    expect($prescription->fresh()->status)->toBe('diproses');

    // 2. Complete prescription (diproses -> selesai & deduct 5 units from stock)
    $completeResp = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/prescriptions/{$prescription->prescription_id}/complete");

    $completeResp->assertOk();
    expect($prescription->fresh()->status)->toBe('selesai');
    expect($ctx['medicine']->fresh()->stock)->toBe($initialStock - 5); // 45
});

test('koas intern staff is forbidden 403 from pharmacy prescription dispensation', function () {
    $ctx = setupStaffWorkflowEnvironment();

    $prescription = Prescription::create([
        'medical_record_id' => MedicalRecord::create([
            'reservation_id' => null,
            'patient_id' => $ctx['patient']->patient_id,
            'doctor_id' => $ctx['doctor']->doctor_id,
            'subjective' => 'Test',
            'objective' => [],
            'assessment' => 'Test',
            'plan' => 'Test',
        ])->medical_record_id,
        'prescription_number' => 'RX-KOAS-DENIED',
        'status' => 'menunggu',
    ]);

    $response = $this->actingAs($ctx['nurseKoasUser'])
        ->postJson("/staff/prescriptions/{$prescription->prescription_id}/process");

    $response->assertForbidden();
});

test('permanent staff can manage medicine master data and adjust stock while koas is forbidden', function () {
    $ctx = setupStaffWorkflowEnvironment();

    // 1. Permanent nurse accesses medicine index
    $indexResp = $this->actingAs($ctx['nurseTetapUser'])
        ->get('/staff/medicines');

    $indexResp->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/Medicines/Index')
            ->has('medicines')
            ->has('stats')
        );

    // 2. Permanent nurse creates a new medicine
    $storeResp = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson('/staff/medicines', [
            'code_medicine' => 'MED-NEW-100',
            'name_medicine' => 'Cefixime 100 mg',
            'type' => 'Kapsul',
            'stock' => 80,
            'unit' => 'Strip',
            'price' => 35000,
        ]);

    $storeResp->assertCreated();
    $newMed = Medicine::where('code_medicine', 'MED-NEW-100')->first();
    expect($newMed)->not->toBeNull()
        ->and($newMed->stock)->toBe(80);

    // 3. Permanent nurse adjusts stock (restocks +20)
    $adjustResp = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/medicines/{$newMed->medicine_id}/adjust-stock", [
            'type' => 'add',
            'amount' => 20,
            'notes' => 'Penerimaan PBF',
        ]);

    $adjustResp->assertOk();
    expect($newMed->fresh()->stock)->toBe(100);

    // 4. Koas intern is forbidden from accessing medicine management
    $koasIndexResp = $this->actingAs($ctx['nurseKoasUser'])
        ->get('/staff/medicines');

    $koasIndexResp->assertForbidden();
});
