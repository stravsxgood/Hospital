<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAdminUserRequest;
use App\Http\Requests\Admin\StoreDoctorUserRequest;
use App\Http\Requests\Admin\StoreNurseUserRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Nurse;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display the User Directory & Provisioning Management Page.
     */
    public function index(IndexAdminUserRequest $request): Response|JsonResponse
    {
        $validated = $request->validated();
        $search = $validated['search'] ?? null;
        $roleFilter = $validated['role'] ?? 'all';
        $statusFilter = $validated['status'] ?? 'all';
        $perPage = (int) ($validated['per_page'] ?? 15);

        $driver = DB::connection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

        $query = User::query()
            ->with([
                'doctor.specialization',
                'doctor.schedules.poli',
                'doctor.schedules.room',
                'nurse',
                'roles',
            ])
            ->when($search, function ($q) use ($search, $likeOp) {
                $term = "%{$search}%";
                $q->where(function ($sub) use ($term, $likeOp) {
                    $sub->where('name', $likeOp, $term)
                        ->orWhere('email', $likeOp, $term)
                        ->orWhereHas('doctor', fn ($d) => $d->where('sip_number', $likeOp, $term)->orWhere('name', $likeOp, $term))
                        ->orWhereHas('nurse', fn ($n) => $n->where('registration_number', $likeOp, $term)->orWhere('name', $likeOp, $term));
                });
            })
            ->when($roleFilter !== 'all', function ($q) use ($roleFilter) {
                match ($roleFilter) {
                    'doctor' => $q->where('role', 'doctor')->orWhereHas('roles', fn ($r) => $r->where('name', 'dpjp-doctor')),
                    'nurse_tetap' => $q->whereHas('nurse', fn ($n) => $n->where('type', 'tetap')),
                    'koas' => $q->whereHas('nurse', fn ($n) => $n->where('type', 'koas')),
                    'nurse' => $q->where('role', 'nurse'),
                    'admin', 'super-admin' => $q->whereIn('role', ['admin', 'super-admin'])->orWhereHas('roles', fn ($r) => $r->where('name', 'super-admin')),
                    'patient' => $q->where('role', 'patient'),
                    default => null,
                };
            })
            ->when($statusFilter !== 'all', function ($q) use ($statusFilter) {
                if ($statusFilter === 'active') {
                    $q->where('is_active', true);
                } elseif ($statusFilter === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->latest('id');

        $users = $query->paginate($perPage)->withQueryString();

        // Master Data untuk Modal Pendaftaran
        $specializations = Specialization::orderBy('name_specialization')->get();
        $polis = Poli::where('status', 'Aktif')->orderBy('name_poli')->get();
        $rooms = Room::orderBy('name_room')->get();

        $stats = [
            'total_users' => User::count(),
            'total_doctors' => Doctor::count(),
            'total_nurses_tetap' => Nurse::where('type', 'tetap')->count(),
            'total_nurses_koas' => Nurse::where('type', 'koas')->count(),
            'total_inactive' => User::where('is_active', false)->count(),
        ];

        $payload = [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
            ],
            'stats' => $stats,
            'specializations' => $specializations,
            'polis' => $polis,
            'rooms' => $rooms,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Users list retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/Users/Index', $payload);
    }

    /**
     * Provision and register a new DPJP Doctor account.
     */
    public function storeDoctor(StoreDoctorUserRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $created = DB::transaction(function () use ($validated) {
            // 1. Create User
            $password = ! empty($validated['password']) ? $validated['password'] : 'Hospital2026!';
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'doctor',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            // 2. Assign Spatie Role
            Role::firstOrCreate(['name' => 'dpjp-doctor', 'guard_name' => 'web']);
            $user->assignRole('dpjp-doctor');

            // 3. Create Doctor Master Profile
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'specialization_id' => $validated['specialization_id'],
                'name' => $validated['name'],
                'sip_number' => $validated['sip_number'],
                'gender' => $validated['gender'],
                'number_phone' => $validated['number_phone'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'join_date' => $validated['join_date'] ?? Carbon::today()->toDateString(),
                'status' => 'aktif',
            ]);

            // 4. Optional Initial Practice Schedule
            if (! empty($validated['create_schedule']) && ! empty($validated['poli_id']) && ! empty($validated['room_id'])) {
                DoctorSchedule::create([
                    'doctor_id' => $doctor->doctor_id,
                    'poli_id' => $validated['poli_id'],
                    'room_id' => $validated['room_id'],
                    'day' => $validated['day'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'quota_day' => $validated['quota_day'] ?? 20,
                    'status' => 'Aktif',
                ]);
            }

            return ['user' => $user, 'doctor' => $doctor, 'temp_password' => $password];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Akun Dokter DPJP berhasil didaftarkan.',
                'data' => $created['doctor'],
                'temp_password' => $created['temp_password'],
            ], 201);
        }

        return redirect()->back()->with('success', "Akun Dokter {$created['doctor']->name} berhasil dibuat dengan password: {$created['temp_password']}");
    }

    /**
     * Provision and register a new Nurse (Tetap / Koas) account.
     */
    public function storeNurse(StoreNurseUserRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $created = DB::transaction(function () use ($validated) {
            // 1. Create User
            $password = ! empty($validated['password']) ? $validated['password'] : 'Hospital2026!';
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'nurse',
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            // 2. Assign Spatie Role
            $roleName = $validated['type'] === 'koas' ? 'koas-intern' : 'staff-pekerja';
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->assignRole($roleName);

            // 3. Create Nurse Master Profile
            $nurse = Nurse::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'registration_number' => $validated['registration_number'] ?? null,
                'type' => $validated['type'],
                'institute' => $validated['institute'] ?? null,
                'gender' => $validated['gender'],
                'date_start' => $validated['date_start'] ?? null,
                'date_end' => $validated['date_end'] ?? null,
            ]);

            return ['user' => $user, 'nurse' => $nurse, 'temp_password' => $password];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Akun Staf/Perawat berhasil didaftarkan.',
                'data' => $created['nurse'],
                'temp_password' => $created['temp_password'],
            ], 201);
        }

        return redirect()->back()->with('success', "Akun Perawat/Koas {$created['nurse']->name} berhasil dibuat dengan password: {$created['temp_password']}");
    }

    /**
     * Toggle user status (Safe Deactivation - Never hard deletes clinical users).
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === $request->user()->id) {
            $msg = 'Anda tidak dapat menonaktifkan akun Super Admin Anda sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        DB::transaction(function () use ($user) {
            $newStatus = ! $user->is_active;
            $user->is_active = $newStatus;
            $user->save();

            // Jika dokter, sesuaikan status master doctor dan jadwalkan libur
            if ($user->doctor) {
                $user->doctor->status = $newStatus ? 'aktif' : 'pensiun';
                $user->doctor->save();

                if (! $newStatus) {
                    DoctorSchedule::where('doctor_id', $user->doctor->doctor_id)
                        ->update(['status' => 'Libur']);
                }
            }
        });

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Status pengguna {$user->name} berhasil {$statusText}.",
                'is_active' => $user->is_active,
            ]);
        }

        return redirect()->back()->with('success', "Status pengguna {$user->name} berhasil {$statusText}.");
    }

    /**
     * Reset user password to default temporary secure credential.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $temporaryPassword = 'Hospital2026!';

        $user->password = Hash::make($temporaryPassword);
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => "Password pengguna {$user->name} berhasil direset.",
                'temporary_password' => $temporaryPassword,
            ]);
        }

        return redirect()->back()->with('success', "Password untuk {$user->name} berhasil direset menjadi: {$temporaryPassword}");
    }
}
