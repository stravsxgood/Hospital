<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasskeyLoginResponse implements PasskeyLoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function toResponse($request): Response
    {
        $user = $request->user();

        $targetUrl = '/patient/dashboard';

        // Staf / Dokter / Perawat / Admin -> arahkan ke dashboard staf
        $isStaff = $user && (
            in_array($user->role, ['doctor', 'nurse', 'admin'], true)
            || (bool) $user->is_doctor
            || $user->doctor()->exists()
            || $user->nurse()->exists()
        );

        if ($isStaff) {
            $targetUrl = '/staff';
        } elseif ($user && ($user->role === 'patient' || $user->patient()->exists())) {
            $targetUrl = '/patient/dashboard';
        } elseif ($user && $user->currentTeam) {
            $targetUrl = $this->redirectPathForCurrentTeam($request, Fortify::redirects('login'));
        }

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $targetUrl], 200)
            : redirect()->intended($targetUrl);
    }

    /**
     * Tentukan rute dashboard tim untuk akun staf/dokter.
     */
    protected function redirectPathForCurrentTeam(Request $request, string $default = '/')
    {
        return $request->user()->currentTeam
            ? route('dashboard', ['current_team' => $request->user()->currentTeam])
            : $default;
    }
}
