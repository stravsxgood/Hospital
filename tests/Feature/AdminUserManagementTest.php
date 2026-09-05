<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
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

test('super admin can navigate paginated users list preserving role and status query parameters', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    // Buat 15 dokter aktif
    User::factory()->count(15)->create([
        'role' => 'doctor',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get('/admin/users?role=doctor&status=active&per_page=10&page=2');
    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Index')
        ->has('users.data', 5)
        ->where('users.current_page', 2)
        ->where('filters.role', 'doctor')
        ->where('filters.status', 'active')
    );
});

test('super admin can soft-delete an inactive user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $targetUser = User::factory()->create([
        'role' => 'patient',
        'is_active' => false,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$targetUser->id}");
    $response->assertSessionHas('success');

    // Pastikan user terhapus secara soft delete
    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
    expect($targetUser->fresh()->trashed())->toBeTrue();
    expect(User::find($targetUser->id))->toBeNull();
    expect(User::withTrashed()->find($targetUser->id))->not->toBeNull();
});

test('super admin cannot delete an active user and receives 422 error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $targetUser = User::factory()->create([
        'role' => 'patient',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->from('/admin/users')
        ->delete("/admin/users/{$targetUser->id}");

    $response->assertSessionHas('error', 'Only inactive accounts can be deleted. Please deactivate the account first.');

    // Pastikan user masih ada dan tidak di-soft delete
    $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'deleted_at' => null]);
});

test('super admin cannot delete their own account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->from('/admin/users')
        ->delete("/admin/users/{$admin->id}");

    $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
});

test('unauthorized user cannot delete user accounts', function () {
    $patient = User::factory()->create(['role' => 'patient']);
    $patient->assignRole('patient');

    $targetUser = User::factory()->create([
        'role' => 'patient',
        'is_active' => false,
    ]);

    $response = $this->actingAs($patient)->delete("/admin/users/{$targetUser->id}");
    $response->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'deleted_at' => null]);
});

test('super admin can permanently delete patient data and account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3201123456789999',
        'name' => 'Pasien Hard Delete',
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-05-05',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$patientUser->id}/force");
    $response->assertSessionHas('success');

    // Pastikan data benar-benar terhapus secara permanen (bukan sekadar soft delete)
    $this->assertDatabaseMissing('users', ['id' => $patientUser->id]);
    $this->assertDatabaseMissing('patient', ['patient_id' => $patient->patient_id]);
    expect(User::withTrashed()->find($patientUser->id))->toBeNull();
});

test('super admin cannot permanently delete non-patient accounts', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    $doctorUser = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($admin)
        ->from('/admin/users')
        ->delete("/admin/users/{$doctorUser->id}/force");

    $response->assertSessionHas('error', 'Penghapusan permanen hanya diizinkan untuk akun dan data pasien.');
    $this->assertDatabaseHas('users', ['id' => $doctorUser->id]);
});
