<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Pastikan roles dasar tersedia
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'dpjp-doctor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff-pekerja', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'koas-intern', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
});

test('non-admin user is forbidden from accessing admin endpoints', function () {
    $patientUser = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($patientUser)->get('/admin/dashboard');
    $response->assertStatus(403);

    $userResponse = $this->actingAs($patientUser)->get('/admin/users');
    $userResponse->assertStatus(403);
});

test('super admin can access dashboard and view users directory', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertStatus(200);

    $usersResponse = $this->actingAs($admin)->get('/admin/users');
    $usersResponse->assertStatus(200);
});

test('super admin can provision a new DPJP doctor with Spatie role and initial schedule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-TEST-01'],
        ['name_specialization' => 'Spesialis Bedah Umum', 'description' => 'Bedah']
    );

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'POL-T01'],
        ['name_poli' => 'Poli Bedah', 'location' => 'Lantai 2', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'R-TEST-01'],
        ['name_room' => 'Ruang Bedah 1', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 2]
    );

    $payload = [
        'name' => 'dr. Amanda Sp.B',
        'email' => 'amanda.bedah@hospital.com',
        'password' => 'CustomPass123!',
        'specialization_id' => $spec->specialization_id,
        'sip_number' => 'SIP-BEDAH-999',
        'gender' => 'Perempuan',
        'number_phone' => '081299998888',
        'alamat' => 'Jl. Kesehatan No. 10',
        'join_date' => '2026-08-01',
        'create_schedule' => true,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'quota_day' => 15,
    ];

    $response = $this->actingAs($admin)->post('/admin/users/doctors', $payload);
    $response->assertSessionHas('success');

    // Verifikasi User dibuat
    $user = User::where('email', 'amanda.bedah@hospital.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('dr. Amanda Sp.B')
        ->and($user->hasRole('dpjp-doctor'))->toBeTrue();

    // Verifikasi Profil Dokter
    $doctor = Doctor::where('user_id', $user->id)->first();
    expect($doctor)->not->toBeNull()
        ->and($doctor->sip_number)->toBe('SIP-BEDAH-999')
        ->and($doctor->status)->toBe('aktif');

    // Verifikasi Jadwal
    $schedule = DoctorSchedule::where('doctor_id', $doctor->doctor_id)->first();
    expect($schedule)->not->toBeNull()
        ->and($schedule->day)->toBe('Senin')
        ->and($schedule->quota_day)->toBe(15);
});

test('super admin can provision a new Nurse (tetap and koas) with appropriate Spatie roles', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    // 1. Perawat Tetap
    $nursePayload = [
        'name' => 'Ns. Ratna Dewi',
        'email' => 'ratna.perawat@hospital.com',
        'type' => 'tetap',
        'registration_number' => 'STR-RATNA-888',
        'gender' => 'Perempuan',
    ];

    $responseNurse = $this->actingAs($admin)->post('/admin/users/nurses', $nursePayload);
    $responseNurse->assertSessionHas('success');

    $nurseUser = User::where('email', 'ratna.perawat@hospital.com')->first();
    expect($nurseUser)->not->toBeNull()
        ->and($nurseUser->hasRole('staff-pekerja'))->toBeTrue();

    // 2. Dokter Muda (Koas)
    $koasPayload = [
        'name' => 'dr. Muda Kevin Pratama',
        'email' => 'kevin.koas@hospital.com',
        'type' => 'koas',
        'registration_number' => 'NIM-FK-2026-001',
        'institute' => 'FK Universitas Indonesia',
        'gender' => 'Laki-laki',
        'date_start' => '2026-08-01',
        'date_end' => '2026-10-31',
    ];

    $responseKoas = $this->actingAs($admin)->post('/admin/users/nurses', $koasPayload);
    $responseKoas->assertSessionHas('success');

    $koasUser = User::where('email', 'kevin.koas@hospital.com')->first();
    expect($koasUser)->not->toBeNull()
        ->and($koasUser->hasRole('koas-intern'))->toBeTrue();
});

test('super admin can toggle user status safely with automatic schedule deactivation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    // Buat dokter dengan jadwal aktif
    $spec = Specialization::firstOrCreate(['code_specialization' => 'SP-TEST-02'], ['name_specialization' => 'Mata', 'description' => 'Mata']);
    $poli = Poli::firstOrCreate(['kode_poli' => 'POL-T02'], ['name_poli' => 'Poli Mata', 'location' => 'Lt 1', 'status' => 'Aktif']);
    $room = Room::firstOrCreate(['code_room' => 'R-TEST-02'], ['name_room' => 'Ruang Mata', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]);

    $doctorUser = User::factory()->create(['role' => 'doctor', 'is_active' => true]);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Farhan Sp.M',
        'sip_number' => 'SIP-MATA-101',
        'gender' => 'Laki-laki',
        'join_date' => now(),
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Rabu',
        'start_time' => '09:00',
        'end_time' => '13:00',
        'quota_day' => 20,
        'status' => 'Aktif',
    ]);

    // Nonaktifkan Akun Dokter
    $response = $this->actingAs($admin)->patch("/admin/users/{$doctorUser->id}/toggle-status");
    $response->assertSessionHas('success');

    $doctorUser->refresh();
    $doctor->refresh();
    $schedule->refresh();

    expect($doctorUser->is_active)->toBeFalse()
        ->and($doctor->status)->toBe('pensiun')
        ->and($schedule->status)->toBe('Libur');
});

test('super admin can reset user password to default temporary credential', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $targetUser = User::factory()->create([
        'password' => Hash::make('OldPassword123'),
    ]);

    $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/reset-password");
    $response->assertSessionHas('success');

    $targetUser->refresh();
    expect(Hash::check('Hospital2026!', $targetUser->password))->toBeTrue();
});
