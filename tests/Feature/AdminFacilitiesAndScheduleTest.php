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
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

test('super admin can create, update, and delete a Poliklinik', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    // 1. Create Poli
    $createResponse = $this->actingAs($admin)->post('/admin/polis', [
        'kode_poli' => 'POL-SARAF',
        'name_poli' => 'Poli Saraf & Neurologi',
        'location'  => 'Lantai 3 Sayap Barat',
        'status'    => 'Aktif',
    ]);
    $createResponse->assertSessionHas('success');

    $poli = Poli::where('kode_poli', 'POL-SARAF')->first();
    expect($poli)->not->toBeNull()
        ->and($poli->name_poli)->toBe('Poli Saraf & Neurologi');

    // 2. Update Poli
    $updateResponse = $this->actingAs($admin)->put("/admin/polis/{$poli->poli_id}", [
        'kode_poli' => 'POL-SARAF',
        'name_poli' => 'Poli Neurologi Terpadu',
        'location'  => 'Lantai 3 Sayap Timur',
        'status'    => 'Aktif',
    ]);
    $updateResponse->assertSessionHas('success');

    $poli->refresh();
    expect($poli->name_poli)->toBe('Poli Neurologi Terpadu');

    // 3. Delete Poli
    $deleteResponse = $this->actingAs($admin)->delete("/admin/polis/{$poli->poli_id}");
    $deleteResponse->assertSessionHas('success');

    expect(Poli::where('poli_id', $poli->poli_id)->exists())->toBeFalse();
});

test('super admin cannot delete poliklinik that has attached schedules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $spec = Specialization::firstOrCreate(['code_specialization' => 'SP-TEST-03'], ['name_specialization' => 'THT', 'description' => 'THT']);
    $poli = Poli::firstOrCreate(['kode_poli' => 'POL-THT-01'], ['name_poli' => 'Poli THT', 'location' => 'Lt 2', 'status' => 'Aktif']);
    $room = Room::firstOrCreate(['code_room' => 'R-THT-01'], ['name_room' => 'Ruang THT', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 2]);

    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id'           => $docUser->id,
        'specialization_id' => $spec->specialization_id,
        'name'              => 'dr. Hendra Sp.THT',
        'sip_number'        => 'SIP-THT-001',
        'gender'            => 'Laki-laki',
        'join_date'         => now(),
        'status'            => 'aktif',
    ]);

    DoctorSchedule::create([
        'doctor_id'  => $doctor->doctor_id,
        'poli_id'    => $poli->poli_id,
        'room_id'    => $room->room_id,
        'day'        => 'Kamis',
        'start_time' => '10:00',
        'end_time'   => '14:00',
        'quota_day'  => 20,
        'status'     => 'Aktif',
    ]);

    // Hapus poli harus ditolak
    $response = $this->actingAs($admin)->delete("/admin/polis/{$poli->poli_id}");
    $response->assertSessionHas('error');

    expect(Poli::where('poli_id', $poli->poli_id)->exists())->toBeTrue();
});

test('super admin can create, update, and manage doctor practice schedule quotas', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $spec = Specialization::firstOrCreate(['code_specialization' => 'SP-TEST-04'], ['name_specialization' => 'Anak', 'description' => 'Anak']);
    $poli = Poli::firstOrCreate(['kode_poli' => 'POL-ANAK'], ['name_poli' => 'Poli Anak', 'location' => 'Lt 1', 'status' => 'Aktif']);
    $room = Room::firstOrCreate(['code_room' => 'R-ANAK-01'], ['name_room' => 'Ruang Anak', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]);

    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id'           => $docUser->id,
        'specialization_id' => $spec->specialization_id,
        'name'              => 'dr. Citra Sp.A',
        'sip_number'        => 'SIP-ANAK-001',
        'gender'            => 'Perempuan',
        'join_date'         => now(),
        'status'            => 'aktif',
    ]);

    // 1. Create Schedule
    $createResponse = $this->actingAs($admin)->post('/admin/schedules', [
        'doctor_id'  => $doctor->doctor_id,
        'poli_id'    => $poli->poli_id,
        'room_id'    => $room->room_id,
        'day'        => 'Jumat',
        'start_time' => '08:00',
        'end_time'   => '11:30',
        'quota_day'  => 25,
        'status'     => 'Aktif',
    ]);
    $createResponse->assertSessionHas('success');

    $schedule = DoctorSchedule::where('doctor_id', $doctor->doctor_id)->where('day', 'Jumat')->first();
    expect($schedule)->not->toBeNull()
        ->and($schedule->quota_day)->toBe(25);

    // 2. Update Schedule
    $updateResponse = $this->actingAs($admin)->put("/admin/schedules/{$schedule->doctor_schedule_id}", [
        'doctor_id'  => $doctor->doctor_id,
        'poli_id'    => $poli->poli_id,
        'room_id'    => $room->room_id,
        'day'        => 'Jumat',
        'start_time' => '08:30',
        'end_time'   => '12:00',
        'quota_day'  => 30,
        'status'     => 'Aktif',
    ]);
    $updateResponse->assertSessionHas('success');

    $schedule->refresh();
    expect($schedule->quota_day)->toBe(30);

    // 3. Delete Schedule
    $deleteResponse = $this->actingAs($admin)->delete("/admin/schedules/{$schedule->doctor_schedule_id}");
    $deleteResponse->assertSessionHas('success');

    expect(DoctorSchedule::where('doctor_schedule_id', $schedule->doctor_schedule_id)->exists())->toBeFalse();
});
