<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    use RedirectsToCurrentTeam;

    /**
     * Create an HTTP response that represents the object.
     *
     * Alur redirect hierarki login SIMRS:
     * 1. Super Admin / Admin -> Arahkan ke /admin/dashboard
     * 2. Tenaga Medis / Staf (Dokter, Perawat Pekerja, Koas) -> Arahkan ke /staff
     * 3. Pasien -> Arahkan ke /patient/dashboard
     * 4. Pengguna dengan Tim Internal -> Arahkan ke /dashboard tim
     * 5. Default Fallback -> Arahkan ke /patient/dashboard
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Catat waktu login terakhir untuk audit & deteksi akun inaktif
        $user->update(['last_login_at' => now()]);

        // 1. Super Admin / Admin -> Arahkan ke Dashboard Admin
        $isAdmin = (bool) $user->is_admin
            || in_array(strtolower(trim((string) $user->role)), ['super-admin', 'admin', 'super admin', 'super_admin', 'administrator'], true)
            || in_array(strtolower(trim((string) ($user->getAttributes()['role'] ?? ''))), ['super-admin', 'admin', 'super admin', 'super_admin', 'administrator'], true)
            || $user->hasAnyRole(['super-admin', 'Super Admin', 'super_admin', 'super admin', 'admin', 'Admin', 'Administrator']);

        if ($isAdmin) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Tenaga Medis / Staff (Dokter, Perawat Tetap, Perawat Koas) -> Arahkan ke Workspace Klinis
        $isStaff = in_array(strtolower(trim((string) $user->role)), ['doctor', 'nurse', 'dpjp-doctor', 'dokter', 'perawat', 'perawat-tetap', 'perawat-koas', 'koas', 'koas-intern', 'staff-pekerja', 'kasir', 'staff'], true)
            || (bool) $user->is_doctor
            || $user->hasAnyRole(['doctor', 'dpjp-doctor', 'Dokter', 'nurse', 'Perawat', 'Kasir', 'perawat-tetap', 'perawat-koas', 'koas', 'koas-intern', 'staff-pekerja', 'staff'])
            || $user->doctor()->exists()
            || $user->nurse()->exists();

        if ($isStaff) {
            return redirect('/staff');
        }

        // 3. Pasien -> Arahkan ke Portal Pasien
        $isPatient = in_array(strtolower(trim((string) $user->role)), ['patient', 'pasien'], true)
            || $user->patient()->exists()
            || $user->hasAnyRole(['patient', 'pasien', 'Pasien', 'Patient']);

        if ($isPatient) {
            return redirect()->route('patient.dashboard');
        }

        // 4. Jika memiliki tim internal aktif
        if ($user->current_team_id && $user->currentTeam) {
            return redirect(
                route('dashboard', ['current_team' => $user->currentTeam?->slug ?? $user->current_team_id])
            );
        }

        // 5. Default Fallback -> Portal Pasien
        return redirect()->route('patient.dashboard');
    }
}
