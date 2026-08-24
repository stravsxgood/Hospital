<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function toResponse($request)
    {
        $user = $request->user();

        // 1. Jika Dokter / Perawat / Staf / Admin -> arahkan ke /staff
        $isStaff = in_array($user->role, ['doctor', 'nurse', 'admin'], true)
            || (bool) $user->is_doctor
            || $user->doctor()->exists()
            || $user->nurse()->exists();

        if ($isStaff) {
            return redirect('/staff');
        }

        // 2. Jika Pasien -> arahkan ke Portal Pasien
        if ($user->role === 'patient' || $user->patient()->exists()) {
            return redirect('/patient/dashboard');
        }

        // 3. Jika punya tim internal -> arahkan ke dashboard tim
        if ($user->current_team_id && $user->currentTeam) {
            return redirect(
                route('dashboard', ['current_team' => $user->currentTeam?->slug ?? $user->current_team_id])
            );
        }

        // 4. Default fallback -> Portal Pasien
        return redirect('/patient/dashboard');
    }
}
