<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->withoutMiddleware([
        ValidateCsrfToken::class,
    ]);

    Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'dpjp-doctor', 'guard_name' => 'web']);
});

function getOrCreateSpecialization(): Specialization
{
    return Specialization::firstOrCreate(
        ['code_specialization' => 'SP-TEST-GEN'],
        ['name_specialization' => 'Spesialis Umum']
    );
}

function createDoctorWithSchedule(string $name, string $day, int $quota = 10, string $status = 'Aktif'): array
{
    $spec = getOrCreateSpecialization();
    $doctorUser = User::factory()->create(['role' => 'doctor']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'name' => $name,
        'specialization_id' => $spec->specialization_id,
        'sip_number' => 'SIP-'.uniqid(),
        'gender' => 'Laki-laki',
        'number_phone' => '08123456789',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name) ?? 'POLI', 0, 4));
    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'POL-'.$code],
        ['name_poli' => 'Poli '.$name, 'location' => 'Lantai 1', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'R-'.$code],
        ['name_room' => 'Ruang '.$name, 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]
    );

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => $day,
        'start_time' => '08:00',
        'end_time' => '12:00',
        'quota_day' => $quota,
        'status' => $status,
    ]);

    return [$doctor, $schedule];
}

test('patient can successfully book an appointment for arbitrary doctors on their scheduled day', function () {
    [$doctor, $schedule] = createDoctorWithSchedule('dr. Anton Pratama', 'Senin', 10);

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Budi',
        'resident_n' => '3201999988880001',
        'gender' => 'Laki-laki',
        'birthday_date' => '1990-01-01',
        'number_phone' => '089988776655',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();

    $payload = [
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => $nextMonday,
        'complaint' => 'Pusing dan demam sejak kemarin',
    ];

    $response = $this->actingAs($patientUser)
        ->post('/appointments', $payload);

    $response->assertSessionHas('success');

    $appointment = Appointment::where('patient_id', $patient->patient_id)
        ->where('doctor_schedule_id', $schedule->doctor_schedule_id)
        ->whereDate('appointment_date', $nextMonday)
        ->first();

    expect($appointment)->not->toBeNull()
        ->and($appointment->status)->toBe('pending')
        ->and($appointment->complaint)->toBe('Pusing dan demam sejak kemarin')
        ->and($appointment->queue_number)->not->toBeNull();
});

test('appointment booking is rejected when selected date does not match doctor schedule day', function () {
    [$doctor, $schedule] = createDoctorWithSchedule('dr. Siti Rahma', 'Rabu', 15);

    $patientUser = User::factory()->create(['role' => 'patient']);
    Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Anak Testing',
        'resident_n' => '3201999988880002',
        'gender' => 'Perempuan',
        'birthday_date' => '2015-05-05',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    $nextThursday = Carbon::now()->next(Carbon::THURSDAY)->toDateString();

    $response = $this->actingAs($patientUser)
        ->post('/appointments', [
            'doctor_schedule_id' => $schedule->doctor_schedule_id,
            'appointment_date' => $nextThursday,
            'complaint' => 'Batuk pilek',
        ]);

    $response->assertSessionHasErrors('appointment_date');
    expect(Appointment::where('doctor_schedule_id', $schedule->doctor_schedule_id)->count())->toBe(0);
});

test('patient can re-book after a previous appointment was cancelled without postgres unique violation', function () {
    [$doctor, $schedule] = createDoctorWithSchedule('dr. Hendra SpPD', 'Jumat', 10);

    $patientUser = User::factory()->create(['role' => 'patient']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Hendra Fan',
        'resident_n' => '3201999988880003',
        'gender' => 'Laki-laki',
        'birthday_date' => '1985-02-02',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    $nextFriday = Carbon::now()->next(Carbon::FRIDAY)->toDateString();

    // 1. Buat janji temu pertama dan batalkan
    Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => $nextFriday,
        'queue_number' => 'POL-TEST-001',
        'complaint' => 'Janji lama yang dibatalkan',
        'status' => 'cancelled',
    ]);

    // 2. Pasien memesan ulang untuk tanggal dan jadwal yang sama
    $response = $this->actingAs($patientUser)
        ->post('/appointments', [
            'doctor_schedule_id' => $schedule->doctor_schedule_id,
            'appointment_date' => $nextFriday,
            'complaint' => 'Mendaftar ulang setelah sembuh sebagian',
        ]);

    $response->assertSessionHas('success');

    $refreshed = Appointment::where('patient_id', $patient->patient_id)
        ->where('doctor_schedule_id', $schedule->doctor_schedule_id)
        ->whereDate('appointment_date', $nextFriday)
        ->first();

    expect($refreshed)->not->toBeNull()
        ->and($refreshed->status)->toBe('pending')
        ->and($refreshed->complaint)->toBe('Mendaftar ulang setelah sembuh sebagian');
});

test('booking is rejected when quota is exceeded', function () {
    [$doctor, $schedule] = createDoctorWithSchedule('dr. Maya SpM', 'Selasa', 1);

    $nextTuesday = Carbon::now()->next(Carbon::TUESDAY)->toDateString();

    // Pasien 1 sudah mengambil kuota 1
    $patientUser1 = User::factory()->create(['role' => 'patient']);
    $patient1 = Patient::create([
        'user_id' => $patientUser1->id,
        'name' => 'Pasien 1 Mata',
        'resident_n' => '3201999988880004',
        'gender' => 'Laki-laki',
        'birthday_date' => '1980-01-01',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    Appointment::create([
        'patient_id' => $patient1->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => $nextTuesday,
        'queue_number' => 'POL-TEST-001',
        'status' => 'confirmed',
    ]);

    // Pasien 2 mencoba mendaftar
    $patientUser2 = User::factory()->create(['role' => 'patient']);
    Patient::create([
        'user_id' => $patientUser2->id,
        'name' => 'Pasien 2 Mata',
        'resident_n' => '3201999988880005',
        'gender' => 'Perempuan',
        'birthday_date' => '1988-08-08',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($patientUser2)
        ->post('/appointments', [
            'doctor_schedule_id' => $schedule->doctor_schedule_id,
            'appointment_date' => $nextTuesday,
            'complaint' => 'Mata buram',
        ]);

    $response->assertSessionHasErrors('appointment_date');
});

test('booking is rejected when doctor schedule is not active (libur)', function () {
    [$doctor, $schedule] = createDoctorWithSchedule('dr. Libur SpA', 'Senin', 10, 'Libur');

    $nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();

    $patientUser = User::factory()->create(['role' => 'patient']);
    Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Pasien Testing Libur',
        'resident_n' => '3201999988880006',
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-01-01',
        'status' => 'active',
        'registration_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($patientUser)
        ->post('/appointments', [
            'doctor_schedule_id' => $schedule->doctor_schedule_id,
            'appointment_date' => $nextMonday,
            'complaint' => 'Pemeriksaan rutin',
        ]);

    $response->assertSessionHasErrors('doctor_schedule_id');
});
