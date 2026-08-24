<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LoginResponse;

test('doctor api login redirects to /staff and returns doctor role', function () {
    $this->withoutMiddleware([
        ValidateCsrfToken::class,
        VerifyCsrfToken::class,
    ]);

    $user = User::factory()->create([
        'email' => 'dr.test@hospital.com',
        'password' => Hash::make('password'),
    ]);

    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-TEST'],
        ['name_specialization' => 'Spesialis Pengujian']
    );

    Doctor::create([
        'user_id' => $user->id,
        'specialization_id' => $spec->specialization_id ?? $spec->id,
        'name' => 'dr. Test Doctor, Sp.PD',
        'sip_number' => 'SIP/999/TEST/2026',
        'gender' => 'Laki-laki',
        'join_date' => '2024-01-01',
        'status' => 'aktif',
    ]);

    expect($user->fresh()->role)->toBe('doctor');
    expect($user->fresh()->is_doctor)->toBeTrue();

    $response = $this->postJson('/api/login', [
        'email' => 'dr.test@hospital.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'redirect_to' => '/staff',
            'data' => [
                'user' => [
                    'role' => 'doctor',
                ],
            ],
        ]);
});

test('patient api login redirects to /patient/dashboard and returns patient role', function () {
    $this->withoutMiddleware([
        ValidateCsrfToken::class,
        VerifyCsrfToken::class,
    ]);

    $user = User::factory()->create([
        'email' => 'patient.test@example.com',
        'password' => Hash::make('password'),
    ]);

    Patient::create([
        'user_id' => $user->id,
        'name' => 'Patient Test',
        'resident_n' => '3301010101010099',
        'gender' => 'Perempuan',
        'birthday_date' => '1995-05-15',
    ]);

    expect($user->fresh()->role)->toBe('patient');
    expect($user->fresh()->is_doctor)->toBeFalse();

    $response = $this->postJson('/api/login', [
        'email' => 'patient.test@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'redirect_to' => '/patient/dashboard',
            'data' => [
                'user' => [
                    'role' => 'patient',
                ],
            ],
        ]);
});

test('doctor web login response redirects to /staff', function () {
    $user = User::factory()->create();

    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-TEST-W'],
        ['name_specialization' => 'Spesialis Web']
    );

    Doctor::create([
        'user_id' => $user->id,
        'specialization_id' => $spec->specialization_id ?? $spec->id,
        'name' => 'dr. Web Doctor, Sp.PD',
        'sip_number' => 'SIP/998/WEB/2026',
        'gender' => 'Laki-laki',
        'join_date' => '2024-01-01',
        'status' => 'aktif',
    ]);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    $response = app(LoginResponse::class)->toResponse($request);

    expect($response->getTargetUrl())->toBe(url('/staff'));
});

test('patient web login response redirects to /patient/dashboard', function () {
    $user = User::factory()->create();

    Patient::create([
        'user_id' => $user->id,
        'name' => 'Web Patient',
        'resident_n' => '3301010101010077',
        'gender' => 'Perempuan',
        'birthday_date' => '1992-02-02',
    ]);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    $response = app(LoginResponse::class)->toResponse($request);

    expect($response->getTargetUrl())->toBe(route('patient.dashboard'));
});
