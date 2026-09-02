<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Poli;
use App\Models\Registration;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;

test('unauthenticated user cannot mutate doctor schedules via API', function () {
    $response = $this->postJson('/api/doctor-schedules', [
        'day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '12:00',
    ]);

    // Wajib ditolak karena belum terotentikasi
    $response->assertUnauthorized();
});

test('patient user cannot view another patients payment record via API (BOLA/IDOR protection)', function () {
    // 1. Buat User Pasien A & B
    $userA = User::factory()->create(['role' => 'patient']);
    $patientA = Patient::create([
        'user_id' => $userA->id,
        'resident_n' => '1234567890123456',
        'name' => 'Pasien A',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-01-01',
        'status' => 'active',
    ]);

    $userB = User::factory()->create(['role' => 'patient']);
    $patientB = Patient::create([
        'user_id' => $userB->id,
        'resident_n' => '6543210987654321',
        'name' => 'Pasien B',
        'gender' => 'Perempuan',
        'birthday_date' => '1992-02-02',
        'status' => 'active',
    ]);

    // 2. Buat Master Medis
    $spec = Specialization::create([
        'code_specialization' => 'SP-UMUM',
        'name_specialization' => 'Umum',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'PLU',
        'name_poli' => 'Poli Umum',
        'location' => 'Lantai 1',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-101',
        'name_room' => 'R101',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'Dr. Budi',
        'sip_number' => 'SIP-001',
        'gender' => 'Laki-laki',
        'join_date' => '2025-01-01',
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'status' => 'Aktif',
    ]);

    // 3. Buat Registrasi & Tagihan Pasien B
    $regB = Registration::create([
        'patient_id' => $patientB->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'registration_date' => now()->toDateString(),
        'status' => 'Menunggu',
    ]);

    $paymentB = Payment::create([
        'registration_id' => $regB->registration_id,
        'payment_date' => now(),
        'payment_total' => 250000,
        'payment_method' => 'Tunai',
        'payment_status' => 'Unpaid',
    ]);

    // 4. Pasien A mencoba mengakses data tagihan milik Pasien B
    $response = $this->actingAs($userA)
        ->getJson("/api/payments/{$paymentB->payment_id}");

    // Harus ditolak dengan status 403 Forbidden
    $response->assertForbidden();
});

test('patient user cannot access another patients SatuSehat FHIR bundle (BOLA/IDOR protection)', function () {
    $userA = User::factory()->create(['role' => 'patient']);
    $patientA = Patient::create([
        'user_id' => $userA->id,
        'resident_n' => '1111222233334444',
        'name' => 'Pasien Alpha',
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-05-05',
        'status' => 'active',
    ]);

    $userB = User::factory()->create(['role' => 'patient']);
    $patientB = Patient::create([
        'user_id' => $userB->id,
        'resident_n' => '5555666677778888',
        'name' => 'Pasien Beta',
        'gender' => 'Perempuan',
        'birthday_date' => '1996-06-06',
        'status' => 'active',
    ]);

    $spec = Specialization::create([
        'code_specialization' => 'SP-GIGI',
        'name_specialization' => 'Gigi',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'PLG',
        'name_poli' => 'Poli Gigi',
        'location' => 'Lantai 2',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-202',
        'name_room' => 'R202',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 2,
    ]);
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'Drg. Siti',
        'sip_number' => 'SIP-002',
        'gender' => 'Perempuan',
        'join_date' => '2025-01-01',
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Selasa',
        'start_time' => '09:00',
        'end_time' => '13:00',
        'status' => 'Aktif',
    ]);

    $appointmentB = Appointment::create([
        'patient_id' => $patientB->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'GIG-001',
        'status' => 'completed',
    ]);

    $medicalRecordB = MedicalRecord::create([
        'reservation_id' => $appointmentB->appointment_id,
        'patient_id' => $patientB->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'subjective' => 'Sakit gigi geraham',
        'objective' => ['tensi' => '120/80'],
        'assessment' => 'K04.0 Pulpitis',
        'plan' => 'Tambal sementara',
    ]);

    // Pasien A mencoba mengakses FHIR bundle Pasien B
    $response = $this->actingAs($userA)
        ->getJson("/api/satusehat/records/{$medicalRecordB->medical_record_id}/fhir-bundle");

    $response->assertForbidden();
});

test('xendit webhook rejects unauthorized requests when token is missing or forged', function () {
    $response = $this->postJson('/api/webhooks/xendit', [
        'id' => 'fake_id',
        'status' => 'PAID',
    ], [
        'x-callback-token' => 'invalid-forged-token',
    ]);

    $response->assertStatus(401);
});

test('patient user cannot access staff dashboard or confirm arrival', function () {
    $patientUser = User::factory()->create(['role' => 'patient']);

    $responseDashboard = $this->actingAs($patientUser)->get('/staff/dashboard');
    $responseDashboard->assertForbidden();

    $responseConfirm = $this->actingAs($patientUser)->postJson('/staff/reservations/1/confirm-arrival');
    $responseConfirm->assertForbidden();
});

test('patient user cannot access doctor queue or call patients', function () {
    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3333444455556666',
        'name' => 'Pasien Echo',
        'gender' => 'Laki-laki',
        'birthday_date' => '2000-01-01',
        'status' => 'active',
    ]);

    $spec = Specialization::create([
        'code_specialization' => 'SP-TEST-Q',
        'name_specialization' => 'Test Poli',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'PLTQ',
        'name_poli' => 'Poli Test',
        'location' => 'Lantai 1',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-TQ',
        'name_room' => 'Room Test',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);
    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $docUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'Dr. Test Queue',
        'sip_number' => 'SIP-TQ',
        'gender' => 'Laki-laki',
        'join_date' => '2025-01-01',
        'status' => 'aktif',
    ]);
    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'status' => 'Aktif',
    ]);
    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'TQ-001',
        'status' => 'pending',
    ]);

    $responseQueue = $this->actingAs($patientUser)->get('/doctor/queue');
    $responseQueue->assertForbidden();

    $responseCall = $this->actingAs($patientUser)->patchJson("/doctor/queue/{$appointment->appointment_id}/call");
    $responseCall->assertForbidden();
});

test('patient user cannot view another patients medical history via doctor patient history endpoint', function () {
    $userA = User::factory()->create(['role' => 'patient']);
    Patient::create([
        'user_id' => $userA->id,
        'resident_n' => '1111000011110000',
        'name' => 'Pasien Charlie',
        'gender' => 'Laki-laki',
        'birthday_date' => '1998-08-08',
        'status' => 'active',
    ]);

    $userB = User::factory()->create(['role' => 'patient']);
    $patientB = Patient::create([
        'user_id' => $userB->id,
        'resident_n' => '2222000022220000',
        'name' => 'Pasien Delta',
        'gender' => 'Perempuan',
        'birthday_date' => '1999-09-09',
        'status' => 'active',
    ]);

    // Pasien A mencoba mengakses riwayat medis Pasien B
    $response = $this->actingAs($userA)
        ->getJson("/doctor/patients/{$patientB->patient_id}/history");

    $response->assertForbidden();

    // Pasien B mengakses riwayat medis miliknya sendiri -> diizinkan
    $responseSelf = $this->actingAs($userB)
        ->getJson("/doctor/patients/{$patientB->patient_id}/history");

    $responseSelf->assertOk();
});
