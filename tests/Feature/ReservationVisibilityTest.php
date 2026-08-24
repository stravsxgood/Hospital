<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;

test('reservations appear in doctor queue and staff dashboard regardless of future appointment date', function () {
    $this->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);

    // 1. Setup Doctor, Patient, and Schedule
    $doctorUser = User::factory()->create([
        'name' => 'dr. Budi Santoso, Sp.A',
        'email' => 'drbudi' . uniqid() . '@test.com',
        'role' => 'doctor',
    ]);

    $spec = Specialization::first() ?? Specialization::create([
        'code_specialization' => 'SP-A',
        'name_specialization' => 'Spesialis Anak (Sp.A)',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'name' => 'dr. Budi Santoso, Sp.A',
        'specialization_id' => $spec->specialization_id ?? $spec->id,
        'sip_number' => 'SIP.503/' . rand(1000, 9999) . '/DS/2026',
        'gender' => 'Laki-laki',
        'number_phone' => '08123456789',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $poli = Poli::first() ?? Poli::create([
        'kode_poli' => 'POLI-ANAK',
        'name_poli' => 'Poli Anak',
        'location'  => 'Gedung B, Lantai 1',
        'status'    => 'Aktif',
    ]);

    $room = Room::first() ?? Room::create([
        'code_room' => 'RM-101',
        'name_room' => 'Ruang 101',
        'type_room' => 'Pemeriksaan',
        'capacity' => 1,
        'floor' => 1,
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

    $patientUser = User::factory()->create([
        'name' => 'Pasien Testing',
        'email' => 'pasien' . uniqid() . '@test.com',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Testing',
        'resident_n' => (string) rand(3201000000000000, 3201999999999999),
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-05-15',
        'number_phone' => '08198765432',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    // 2. Buat reservasi tanggal mendatang (misal 3 hari ke depan)
    $futureDate = Carbon::today()->addDays(3)->toDateString();

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => $futureDate,
        'queue_number' => 'POLI-ANAK-001',
        'complaint' => 'Demam dan batuk pilek',
        'status' => 'pending',
    ]);

    // 3. Akses Doctor Queue sebagai dokter yang bersangkutan
    $response = $this->actingAs($doctorUser)
        ->get('/doctor/queue?schedule_id=' . $schedule->doctor_schedule_id . '&date=' . $futureDate);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('doctor/QueueBoard')
        ->has('appointments', 1)
        ->where('appointments.0.queue_number', 'POLI-ANAK-001')
        ->where('appointments.0.complaint', 'Demam dan batuk pilek')
    );

    // 4. Akses Doctor Queue dengan filter 'all'
    $responseAll = $this->actingAs($doctorUser)
        ->get('/doctor/queue?schedule_id=' . $schedule->doctor_schedule_id . '&date=all');

    $responseAll->assertOk();
    $responseAll->assertInertia(fn ($page) => $page
        ->component('doctor/QueueBoard')
        ->has('appointments', 1)
        ->where('appointments.0.queue_number', 'POLI-ANAK-001')
    );

    // 5. Akses Staff Dashboard dan pastikan reservasi muncul di recentAppointments
    $staffResponse = $this->actingAs($doctorUser)
        ->get('/staff');

    $staffResponse->assertOk();
    $staffResponse->assertInertia(fn ($page) => $page
        ->component('StaffDashboard')
        ->has('recentAppointments')
        ->where('recentAppointments.0.queue_number', 'POLI-ANAK-001')
    );

    // 6. Uji Aksi Panggil Pasien (callPatient) -> status: in_progress
    $callResponse = $this->actingAs($doctorUser)
        ->withSession(['_token' => 'test-csrf-token'])
        ->patch('/doctor/queue/' . $appointment->appointment_id . '/call', [
            '_token' => 'test-csrf-token',
        ]);

    $callResponse->assertRedirect();
    $appointment->refresh();
    expect($appointment->status)->toBe('in_progress');

    // 7. Uji Aksi Selesai Konsultasi (completeConsultation) -> status: completed
    $completeResponse = $this->actingAs($doctorUser)
        ->withSession(['_token' => 'test-csrf-token'])
        ->patch('/doctor/queue/' . $appointment->appointment_id . '/complete', [
            '_token' => 'test-csrf-token',
        ]);

    $completeResponse->assertRedirect();
    $appointment->refresh();
    expect($appointment->status)->toBe('completed');

    // 8. Uji Aksi Lewati Pasien (skipPatient) -> status: pending
    $skipResponse = $this->actingAs($doctorUser)
        ->withSession(['_token' => 'test-csrf-token'])
        ->patch('/doctor/queue/' . $appointment->appointment_id . '/skip', [
            '_token' => 'test-csrf-token',
        ]);

    $skipResponse->assertRedirect();
    $appointment->refresh();
    expect($appointment->status)->toBe('pending');

    // 9. Uji Pemanggilan Berulang Kali (Repeat Calling)
    $firstCall = $this->actingAs($doctorUser)
        ->withSession(['_token' => 'test-csrf-token'])
        ->patch('/doctor/queue/' . $appointment->appointment_id . '/call', [
            '_token' => 'test-csrf-token',
        ]);
    $firstCall->assertRedirect();
    $appointment->refresh();
    $firstUpdatedAt = $appointment->updated_at;
    expect($appointment->status)->toBe('in_progress');

    // Majukan waktu lalu panggil ulang
    Carbon::setTestNow(now()->addSeconds(2));
    $repeatCall = $this->actingAs($doctorUser)
        ->withSession(['_token' => 'test-csrf-token'])
        ->patch('/doctor/queue/' . $appointment->appointment_id . '/call', [
            '_token' => 'test-csrf-token',
        ]);
    $repeatCall->assertRedirect();
    $appointment->refresh();
    expect($appointment->status)->toBe('in_progress');
    expect($appointment->updated_at->gte($firstUpdatedAt))->toBeTrue();
    Carbon::setTestNow(); // Reset test time
});
