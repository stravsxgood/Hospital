<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated with phone number', function () {
    $user = User::factory()->create();
    $patient = Patient::create([
        'user_id' => $user->id,
        'resident_n' => '3201123456789012',
        'name' => $user->name,
        'gender' => 'Laki-laki',
        'birthday_date' => '1995-05-15',
        'number_phone' => '08123456789',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User Updated',
            'email' => 'updated@example.com',
            'phone' => '08987654321',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    $patient->refresh();

    expect($user->name)->toBe('Test User Updated');
    expect($user->email)->toBe('updated@example.com');
    expect($user->email_verified_at)->toBeNull();
    expect($patient->number_phone)->toBe('08987654321');
    expect($patient->name)->toBe('Test User Updated');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('password can be updated with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password-123'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'old-password-123',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});

test('password update fails when current password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password-123'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('profile.password.update'), [
            'current_password' => 'incorrect-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('correct-password-123', $user->fresh()->password))->toBeTrue();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh()->trashed())->toBeTrue();
    expect(User::find($user->id))->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
