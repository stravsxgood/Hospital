<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_board_renders_and_returns_live_data(): void
    {
        $spec = Specialization::create([
            'code_specialization' => 'SPEC-ANAK',
            'name_specialization' => 'Spesialis Anak',
            'description'         => 'Dokter Spesialis Anak',
        ]);

        $poli = Poli::create([
            'kode_poli' => 'POLI-ANAK',
            'name_poli' => 'Poli Anak',
            'location'  => 'Lantai 1',
            'status'    => 'Aktif',
        ]);

        $room = Room::create([
            'code_room' => 'RM-101',
            'name_room' => 'Ruang 101',
            'type_room' => 'Pemeriksaan',
            'capacity'  => 1,
            'floor'     => 1,
        ]);

        $doctorUser = User::factory()->create([
            'role'  => 'doctor',
            'name'  => 'dr. Sarah Sp.A',
            'email' => 'sarah@hospital.com',
        ]);

        $doctor = Doctor::create([
            'user_id'           => $doctorUser->id,
            'specialization_id' => $spec->specialization_id,
            'name'              => 'dr. Sarah Sp.A',
            'sip_number'        => 'SIP-12345',
            'gender'            => 'Perempuan',
            'number_phone'      => '08123456789',
            'email'             => 'sarah@hospital.com',
            'alamat'            => 'Jl. Dokter Sehat No. 1',
            'join_date'         => now()->toDateString(),
            'status'            => 'aktif',
        ]);

        $schedule = DoctorSchedule::create([
            'doctor_id'    => $doctor->doctor_id,
            'poli_id'      => $poli->poli_id,
            'room_id'      => $room->room_id,
            'day'          => 'Senin',
            'start_time'   => '08:00',
            'end_time'     => '12:00',
            'quota_day'    => 30,
            'status'       => 'Aktif',
        ]);

        $patientUser = User::factory()->create([
            'role' => 'patient',
            'name' => 'Ahmad Pasien',
        ]);

        $patient = Patient::create([
            'user_id'           => $patientUser->id,
            'resident_n'        => '3201234567890001',
            'name'              => 'Ahmad Pasien',
            'gender'            => 'Laki-laki',
            'birthday_date'     => '1995-05-15',
            'address'           => 'Jl. Sehat No. 10',
            'number_phone'      => '081298765432',
            'registration_date' => now()->toDateString(),
            'status'            => 'active',
        ]);

        // Buat janji temu berstatus in_progress
        $appointment = Appointment::create([
            'doctor_schedule_id' => $schedule->doctor_schedule_id,
            'patient_id'         => $patient->patient_id,
            'appointment_date'   => '2026-09-01',
            'queue_number'       => 'POLI-ANAK-001',
            'complaint'          => 'Demam tinggi',
            'status'             => 'in_progress',
        ]);

        // 1. Uji halaman /display
        $response = $this->get('/display');
        $response->assertStatus(200);

        // 2. Uji endpoint liveData /display/live-data
        $liveResponse = $this->getJson('/display/live-data');
        $liveResponse->assertStatus(200)
            ->assertJsonPath('latestCalled.queue_number', 'POLI-ANAK-001')
            ->assertJsonPath('latestCalled.patient_name', 'Ahmad Pasien')
            ->assertJsonPath('latestCalled.poli_name', 'Poli Anak')
            ->assertJsonPath('latestCalled.room_name', 'Ruang 101');

        $clinics = $liveResponse->json('clinics');
        $this->assertNotEmpty($clinics);
        $this->assertEquals('POLI-ANAK-001', $clinics[0]['current_calling']);
    }
}
