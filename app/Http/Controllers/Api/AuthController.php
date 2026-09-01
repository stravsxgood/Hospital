<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrasi khusus Pasien Baru (Users + Patient profile)
     */
    public function registerPatient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'resident_n' => 'required|string|size:16|unique:patient,resident_n',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birthday_date' => 'required|date',
            'address' => 'nullable|string',
            'number_phone' => 'nullable|string|max:15',
            'disease' => 'nullable|string',
        ]);

        $result = DB::transaction(function () use ($validated) {
            // 1. Buat Akun Login
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'patient',
                'is_active' => true,
            ]);

            // 2. Buat Profil Pasien
            $patient = Patient::create([
                'user_id' => $user->id,
                'resident_n' => $validated['resident_n'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'birthday_date' => $validated['birthday_date'],
                'address' => $validated['address'] ?? null,
                'number_phone' => $validated['number_phone'] ?? null,
                'disease' => $validated['disease'] ?? null,
                'status' => 'active',
            ]);

            // Hapus semua token lama sebelum membuat token baru saat login
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'patient' => $patient,
                'token' => $token,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi pasien berhasil.',
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'data' => [
                'user' => $result['user'],
                'profile' => $result['patient'],
            ],
        ], 201);
    }

    /**
     * Login untuk Semua Role (Admin, Doctor, Nurse, Patient)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Validasi Kredensial & Status Akun
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kombinasi email dan password tidak sesuai.'],
            ]);
        }

        if ($user->is_active === false || $user->is_active === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda dinonaktifkan. Hubungi admin.',
            ], 403);
        }

        // 2. Aktifkan Sesi Web Laravel (Wajib untuk Inertia & Middleware Auth)
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 3. Buat Token Sanctum untuk Request API
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $profile = match ($user->role) {
            'patient' => $user->patient,
            'doctor' => $user->doctor?->load('specialization'),
            'nurse' => $user->nurse,
            default => null,
        };

        // 4. Tentukan URL Tujuan Berdasarkan Hierarki Role
        $isAdmin = (bool) $user->is_admin
            || in_array(strtolower(trim((string) $user->role)), ['super-admin', 'admin', 'super admin', 'super_admin', 'administrator'], true)
            || in_array(strtolower(trim((string) ($user->getAttributes()['role'] ?? ''))), ['super-admin', 'admin', 'super admin', 'super_admin', 'administrator'], true)
            || $user->hasAnyRole(['super-admin', 'Super Admin', 'super_admin', 'super admin', 'admin', 'Admin', 'Administrator']);

        $isStaff = in_array(strtolower(trim((string) $user->role)), ['doctor', 'nurse', 'dpjp-doctor', 'dokter', 'perawat', 'perawat-tetap', 'perawat-koas', 'koas', 'koas-intern', 'staff-pekerja', 'kasir', 'staff'], true)
            || (bool) $user->is_doctor
            || $user->hasAnyRole(['doctor', 'dpjp-doctor', 'Dokter', 'nurse', 'Perawat', 'Kasir', 'perawat-tetap', 'perawat-koas', 'koas', 'koas-intern', 'staff-pekerja', 'staff'])
            || $user->doctor()->exists()
            || $user->nurse()->exists();

        if ($isAdmin) {
            $redirectTo = '/admin/dashboard';
        } elseif ($isStaff) {
            $redirectTo = '/staff';
        } else {
            $redirectTo = '/patient/dashboard';
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'redirect_to' => $redirectTo,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'raw_role' => $user->getAttributes()['role'] ?? null,
                    'is_admin' => $isAdmin,
                    'is_doctor' => (bool) $user->is_doctor,
                    'roles' => $user->roles->pluck('name')->all(),
                ],
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Mengambil Profil Pengguna yang Sedang Login
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $profile = match ($user->role) {
            'patient' => $user->patient,
            'doctor' => $user->doctor?->load('specialization'),
            'nurse' => $user->nurse,
            default => null,
        };

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Logout & Revoke Token Saat Ini
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout.',
        ]);
    }
}
