<?php

declare(strict_types=1);

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAuditLog;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'dpjp-doctor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff-pekerja', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
});

function createSuperAdmin(): User
{
    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('super-admin');

    return $admin;
}

test('admin users table paginates exactly 10 records per page', function () {
    $admin = createSuperAdmin();
    // Buat 25 user pasien tambahan
    User::factory()->count(25)->create(['role' => 'patient']);

    $response = $this->actingAs($admin)->get('/admin/users');
    $response->assertStatus(200);

    $page = $response->viewData('page');
    $users = $page['props']['users'];

    expect($users['per_page'])->toBe(10)
        ->and($users['current_page'])->toBe(1)
        ->and(count($users['data']))->toBe(10)
        ->and($users['last_page'])->toBeGreaterThanOrEqual(3);
});

test('admin users navigation to page 2 loads distinct subsequent 10 records', function () {
    $admin = createSuperAdmin();
    User::factory()->count(25)->create(['role' => 'patient']);

    $page1Response = $this->actingAs($admin)->get('/admin/users?page=1');
    $page1 = $page1Response->viewData('page')['props']['users'];
    $page1Ids = collect($page1['data'])->pluck('id')->all();

    $page2Response = $this->actingAs($admin)->get('/admin/users?page=2');
    $page2 = $page2Response->viewData('page')['props']['users'];
    $page2Ids = collect($page2['data'])->pluck('id')->all();

    expect($page2['per_page'])->toBe(10)
        ->and($page2['current_page'])->toBe(2)
        ->and(count($page2['data']))->toBe(10);

    // Pastikan data di halaman 2 tidak tumpang tindih dengan halaman 1
    $overlap = array_intersect($page1Ids, $page2Ids);
    expect($overlap)->toBeEmpty();
});

test('admin users pagination links preserve filter query parameters', function () {
    $admin = createSuperAdmin();
    User::factory()->count(15)->create(['role' => 'patient', 'name' => 'Testing Search Target']);

    $response = $this->actingAs($admin)->get('/admin/users?role=patient&search=Testing');
    $response->assertStatus(200);

    $page = $response->viewData('page');
    $users = $page['props']['users'];

    // Cek bahwa link pagination memuat parameter search dan role
    $page2Link = collect($users['links'])->firstWhere('page', 2);
    expect($page2Link)->not->toBeNull()
        ->and($page2Link['url'])->toContain('role=patient')
        ->and($page2Link['url'])->toContain('search=Testing');
});

test('admin audit logs endpoint paginates exactly 10 records per page', function () {
    $admin = createSuperAdmin();

    // Setup Patient & Medical Record untuk relasi audit log
    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Log',
        'resident_n' => '3201888877770001',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-01-01',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-LOG-01'],
        ['name_specialization' => 'Spesialis Log']
    );

    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $docUser->id,
        'name' => 'dr. Log',
        'specialization_id' => $spec->specialization_id,
        'sip_number' => 'SIP-LOG-01',
        'gender' => 'Laki-laki',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $medRecord = MedicalRecord::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'subjective' => 'Keluhan tes audit',
        'objective' => ['td' => '120/80'],
        'assessment' => 'Kondisi stabil',
        'plan' => 'Observasi',
    ]);

    // Buat 15 catatan audit
    for ($i = 0; $i < 15; $i++) {
        MedicalRecordAuditLog::create([
            'medical_record_id' => $medRecord->medical_record_id,
            'user_id' => $admin->id,
            'action' => 'view',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/Test',
        ]);
    }

    $response = $this->actingAs($admin)->get('/admin/audit-logs');
    $response->assertStatus(200);

    $page = $response->viewData('page');
    $logs = $page['props']['logs'];

    expect($logs['per_page'])->toBe(10)
        ->and($logs['current_page'])->toBe(1)
        ->and(count($logs['data']))->toBe(10)
        ->and($logs['last_page'])->toBe(2);
});

test('admin doctor schedules paginates exactly 10 records per page', function () {
    $admin = createSuperAdmin();

    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-SCH-01'],
        ['name_specialization' => 'Spesialis Jadwal']
    );

    $docUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $docUser->id,
        'name' => 'dr. Multi Jadwal',
        'specialization_id' => $spec->specialization_id,
        'sip_number' => 'SIP-SCH-01',
        'gender' => 'Laki-laki',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'POL-SCH'],
        ['name_poli' => 'Poli Jadwal', 'location' => 'Lantai 1', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'R-SCH'],
        ['name_room' => 'Ruang Jadwal', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]
    );

    // Buat 15 jadwal
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    for ($i = 0; $i < 15; $i++) {
        DoctorSchedule::create([
            'doctor_id' => $doctor->doctor_id,
            'poli_id' => $poli->poli_id,
            'room_id' => $room->room_id,
            'day' => $days[$i % 5],
            'start_time' => sprintf('%02d:00', 8 + ($i % 8)),
            'end_time' => sprintf('%02d:00', 12 + ($i % 8)),
            'quota_day' => 10,
            'status' => 'Aktif',
        ]);
    }

    $response = $this->actingAs($admin)->get('/admin/schedules');
    $response->assertStatus(200);

    $page = $response->viewData('page');
    $schedules = $page['props']['schedules'];

    expect($schedules['per_page'])->toBe(10)
        ->and($schedules['current_page'])->toBe(1)
        ->and(count($schedules['data']))->toBe(10)
        ->and($schedules['last_page'])->toBe(2);
});
