<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles profile and password management for authenticated users in the Hospital Population system.
 *
 * Supports updating personal details, managing contact phone numbers across patient/doctor entities,
 * resetting credentials securely, and account decommissioning.
 */
class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile edit page.
     *
     * @param  Request  $request  Incoming HTTP request containing user session
     * @return Response Inertia view rendering Profile/Edit
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        assert($user instanceof User);

        $patient = $user->patient;
        $doctor = $user->doctor;

        $phone = null;
        if ($patient !== null) {
            $phone = $patient->number_phone;
        } elseif ($doctor !== null) {
            $phone = $doctor->number_phone;
        }

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $phone,
                'role' => $user->role,
                'is_doctor' => $user->is_doctor,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            ],
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the authenticated user's profile information and contact details.
     *
     * @param  ProfileUpdateRequest  $request  Validated profile update payload
     * @return RedirectResponse Redirects back with flash notification
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $validated = $request->validated();
        assert(is_string($validated['name']));
        assert(is_string($validated['email']));

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $phone = isset($validated['phone']) && is_string($validated['phone']) ? $validated['phone'] : null;

        if ($user->patient !== null) {
            $user->patient->update([
                'name' => $validated['name'],
                'number_phone' => $phone,
            ]);
        } elseif ($user->doctor !== null) {
            $user->doctor->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'number_phone' => $phone,
            ]);
        } elseif ($user->nurse !== null) {
            $user->nurse->update([
                'name' => $validated['name'],
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Profil berhasil diperbarui.',
        ]);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  PasswordUpdateRequest  $request  Validated password payload
     * @return RedirectResponse Redirects back with flash notification
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $validated = $request->validated();
        assert(is_string($validated['password']));

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);

        return redirect()->route('profile.edit')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Delete the authenticated user's account and invalidate session.
     *
     * @param  Request  $request  Incoming HTTP request containing user session
     * @return RedirectResponse Redirects to home page
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        assert($user instanceof User);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
