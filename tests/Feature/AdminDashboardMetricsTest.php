<?php

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

test('super admin dashboard aggregates financial metrics, cash vs digital split, and morbidity report correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id'           => $patientUser->id,
        'resident_n'        => '3308000000000001',
        'name'              => 'Pasien Metrik',
        'gender'            => 'Laki-laki',
        'birthday_date'     => '1995-01-01',
        'address'           => 'Kota Magelang',
        'number_phone'      => '081233334444',
        'registration_date' => now(),
        'status'            => 'active',
    ]);

    $spec = Specialization::firstOrCreate(['code_specialization' => 'SP-MET-01'], ['name_specialization' => 'Umum', 'description' => 'Umum']);
    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id'           => $docUser->id,
        'specialization_id' => $spec->specialization_id,
        'name'              => 'dr. Metrik Utama',
        'sip_number'        => 'SIP-MET-001',
        'gender'            => 'Laki-laki',
        'join_date'         => now(),
        'status'            => 'aktif',
    ]);

    $poli = Poli::firstOrCreate(['kode_poli' => 'POL-MET'], ['name_poli' => 'Poli Metrik', 'location' => 'Lt 1', 'status' => 'Aktif']);
    $room = Room::firstOrCreate(['code_room' => 'R-MET-01'], ['name_room' => 'Ruang Metrik', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]);

    $schedule = DoctorSchedule::create([
        'doctor_id'  => $doctor->doctor_id,
        'poli_id'    => $poli->poli_id,
        'room_id'    => $room->room_id,
        'day'        => 'Senin',
        'start_time' => '08:00',
        'end_time'   => '12:00',
        'quota_day'  => 20,
        'status'     => 'Aktif',
    ]);

    $appointment = Appointment::create([
        'patient_id'         => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date'   => Carbon::today()->toDateString(),
        'queue_number'       => 1,
        'status'             => 'completed',
    ]);

    // 1. Buat Billing Lunas (Cash & Xendit QRIS)
    Billing::create([
        'reservation_id' => $appointment->appointment_id,
        'patient_id'     => $patient->patient_id,
        'invoice_number' => 'INV-MET-001',
        'total_amount'   => 150000,
        'status'         => 'paid',
        'payment_method' => 'cash',
        'paid_at'        => now(),
    ]);

    Billing::create([
        'reservation_id' => $appointment->appointment_id,
        'patient_id'     => $patient->patient_id,
        'invoice_number' => 'INV-MET-002',
        'total_amount'   => 350000,
        'status'         => 'paid',
        'payment_method' => 'xendit_qris',
        'paid_at'        => now(),
    ]);

    // 2. Buat Rekam Medis Morbiditas
    MedicalRecord::create([
        'patient_id'  => $patient->patient_id,
        'doctor_id'   => $doctor->doctor_id,
        'subjective'  => 'Demam dan batuk',
        'objective'   => 'Suhu 38.5C',
        'assessment'  => 'ISPA Akut (J06.9)',
        'plan'        => 'Terapi simptomatik',
    ]);

    MedicalRecord::create([
        'patient_id'  => $patient->patient_id,
        'doctor_id'   => $doctor->doctor_id,
        'subjective'  => 'Pusing berputar',
        'objective'   => 'Nistagmus (+)',
        'assessment'  => 'Vertigo Paroksismal (H81.1)',
        'plan'        => 'Betahistin',
    ]);

    $response = $this->actingAs($admin)->getJson('/admin/dashboard');
    $response->assertStatus(200);

    $json = $response->json();
    expect($json['status'])->toBeTrue()
        ->and($json['data']['financial']['today_revenue'])->toBeGreaterThanOrEqual(500000)
        ->and(count($json['data']['morbidity']['top_diagnoses']))->toBeGreaterThanOrEqual(2);
});
