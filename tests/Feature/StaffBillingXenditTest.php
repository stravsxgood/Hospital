<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('services.xendit.secret_key', 'mock_xendit_key_for_testing');
    Config::set('services.xendit.webhook_token', 'valid-webhook-secret-token');
});

test('authorized nurse can calculate billing amount automatically', function () {
    $nurseUser = User::factory()->create(['role' => 'nurse']);
    Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Suster Ani',
        'registration_number' => 'REG-001',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3201123456780001',
        'name' => 'Pasien Budi',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-05-12',
        'status' => 'active',
    ]);

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
        'name' => 'dr. Hendra',
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

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'A-001',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($nurseUser)
        ->getJson("/staff/billing/calculate/{$appointment->appointment_id}");

    $response->assertOk()
        ->assertJson([
            'status' => true,
            'appointment_id' => $appointment->appointment_id,
            'consultation_fee' => 150000,
            'admin_fee' => 25000,
            'total_amount' => 175000,
        ]);
});

test('authorized nurse can create xendit invoice billing for appointment', function () {
    $nurseUser = User::factory()->create(['role' => 'nurse']);
    Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Suster Ani',
        'registration_number' => 'REG-002',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3201123456780002',
        'name' => 'Pasien Siti',
        'gender' => 'Perempuan',
        'birthday_date' => '1992-07-20',
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
        'code_room' => 'RM-102',
        'name_room' => 'R102',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'drg. Maya',
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

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'G-002',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($nurseUser)
        ->postJson('/staff/billing', [
            'appointment_id' => $appointment->appointment_id,
            'amount' => 175000,
            'payment_type' => 'invoice',
            'description' => 'Tagihan Rawat Jalan Poli Gigi',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'Tagihan Xendit berhasil dibuat.',
        ]);

    $this->assertDatabaseHas('billing', [
        'reservation_id' => $appointment->appointment_id,
        'patient_id' => $patient->patient_id,
        'total_amount' => 175000,
        'status' => 'pending',
    ]);

    $billing = Billing::where('reservation_id', $appointment->appointment_id)->first();
    expect($billing)->not->toBeNull()
        ->and($billing->external_id)->toStartWith('INV-'.$appointment->appointment_id)
        ->and($billing->invoice_url)->not->toBeEmpty();
});

test('validation prevents duplicate active billings for the same appointment', function () {
    $nurseUser = User::factory()->create(['role' => 'nurse']);
    Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Suster Ani',
        'registration_number' => 'REG-003',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3201123456780003',
        'name' => 'Pasien Roni',
        'gender' => 'Laki-laki',
        'birthday_date' => '1988-02-14',
        'status' => 'active',
    ]);

    $spec = Specialization::create([
        'code_specialization' => 'SP-ANAK',
        'name_specialization' => 'Anak',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'PLA',
        'name_poli' => 'Poli Anak',
        'location' => 'Lantai 1',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-103',
        'name_room' => 'R103',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Sp.A',
        'sip_number' => 'SIP-003',
        'gender' => 'Laki-laki',
        'join_date' => '2025-01-01',
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Rabu',
        'start_time' => '10:00',
        'end_time' => '14:00',
        'status' => 'Aktif',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'A-003',
        'status' => 'completed',
    ]);

    // Buat billing pertama
    Billing::create([
        'reservation_id' => $appointment->appointment_id,
        'appointment_id' => $appointment->appointment_id,
        'patient_id' => $patient->patient_id,
        'invoice_number' => 'INV-EXISTING-123',
        'external_id' => 'INV-EXISTING-123',
        'total_amount' => 200000,
        'status' => 'pending',
    ]);

    // Coba buat billing kedua untuk appointment yang sama
    $response = $this->actingAs($nurseUser)
        ->postJson('/staff/billing', [
            'appointment_id' => $appointment->appointment_id,
            'amount' => 200000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_id']);
});

test('xendit webhook marks billing as PAID and completes appointment for INV pattern', function () {
    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3201123456780004',
        'name' => 'Pasien Dika',
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-11-11',
        'status' => 'active',
    ]);

    $spec = Specialization::create([
        'code_specialization' => 'SP-MATA',
        'name_specialization' => 'Mata',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'PLM',
        'name_poli' => 'Poli Mata',
        'location' => 'Lantai 2',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-104',
        'name_room' => 'R104',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 2,
    ]);
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Sp.M',
        'sip_number' => 'SIP-004',
        'gender' => 'Laki-laki',
        'join_date' => '2025-01-01',
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Kamis',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'status' => 'Aktif',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'M-004',
        'status' => 'in_progress',
    ]);

    $externalId = 'INV-'.$appointment->appointment_id.'-'.time();

    $billing = Billing::create([
        'reservation_id' => $appointment->appointment_id,
        'appointment_id' => $appointment->appointment_id,
        'patient_id' => $patient->patient_id,
        'invoice_number' => $externalId,
        'external_id' => $externalId,
        'total_amount' => 350000,
        'status' => 'pending',
        'xendit_invoice_id' => 'xnd_inv_test_999',
    ]);

    $response = $this->postJson('/webhooks/xendit', [
        'id' => 'xnd_inv_test_999',
        'external_id' => $externalId,
        'status' => 'PAID',
        'payment_method' => 'QRIS',
        'paid_amount' => 350000,
    ], [
        'x-callback-token' => 'valid-webhook-secret-token',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => true,
        ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid')
        ->and($billing->paid_at)->not->toBeNull()
        ->and($billing->payment_method)->toBe('QRIS');

    $appointment->refresh();
    expect($appointment->status)->toBe('completed');
});
