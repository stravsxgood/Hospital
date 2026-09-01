<?php

use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LoginResponse;
use Spatie\Permission\Models\Role;

test('admin web login response redirects to admin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('super admin with spatie role redirects to admin dashboard', function () {
    $role = Role::findOrCreate('super-admin', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('super admin with title case space spatie role redirects to admin dashboard', function () {
    $role = Role::findOrCreate('Super Admin', 'web');
    $user = User::factory()->create(['role' => 'admin']);
    $user->assignRole($role);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('admin api login redirects to /admin/dashboard', function () {
    $this->withoutMiddleware([
        ValidateCsrfToken::class,
        VerifyCsrfToken::class,
    ]);

    User::factory()->create([
        'email' => 'admin.test@hospital.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin.test@hospital.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'redirect_to' => '/admin/dashboard',
            'data' => [
                'user' => [
                    'role' => 'admin',
                ],
            ],
        ]);
});

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

test('nurse web login response redirects to /staff', function () {
    $user = User::factory()->create([
        'role' => 'nurse',
    ]);

    Nurse::create([
        'user_id' => $user->id,
        'name' => 'Nurse Tester',
        'registration_number' => 'STR-NURSE-999',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $request = Request::create('/login', 'POST');
    $request->setUserResolver(fn () => $user);

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(url('/staff'));
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

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
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

    /** @var RedirectResponse $response */
    $response = app(LoginResponse::class)->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(route('patient.dashboard'));
});
