<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;

test('authenticated patient can view their appointments page', function () {
    $user = User::factory()->create();
    $patient = Patient::create([
        'user_id' => $user->id,
        'resident_n' => '3201999988887777',
        'name' => $user->name,
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-01-01',
        'number_phone' => '081234567890',
    ]);

    $spec = Specialization::create(['code_specialization' => 'PD', 'name_specialization' => 'Penyakit Dalam']);
    $poli = Poli::create([
        'kode_poli' => 'INT',
        'name_poli' => 'Poli Penyakit Dalam',
        'location' => 'Lantai 1 Sayap Barat',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'R101',
        'name_room' => 'Ruang 101',
        'type_room' => 'Pemeriksaan',
        'capacity' => 1,
        'floor' => 1,
    ]);

    $doctorUser = User::factory()->create();
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Budi Santoso, Sp.PD',
        'sip_number' => 'SIP-12345-PD',
        'gender' => 'Laki-laki',
        'join_date' => now(),
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
        'quota_day' => 20,
        'status' => 'Aktif',
    ]);

    Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'INT-001',
        'complaint' => 'Demam dan pusing',
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('my'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('MyAppointments')
        ->has('appointments', 1)
        ->where('appointments.0.queue_number', 'INT-001')
    );
});
