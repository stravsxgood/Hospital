<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function toResponse($request): Response
    {
        $user = $request->user();

        $targetUrl = '/patient/dashboard';

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
}
