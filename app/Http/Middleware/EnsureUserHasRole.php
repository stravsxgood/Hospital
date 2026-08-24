<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Global super-admin / admin bypass
        if ($user->hasRole('super-admin') || $user->role === 'super-admin' || $user->role === 'admin') {
            return $next($request);
        }

        // Flatten any comma-separated roles passed (e.g., 'role:super-admin,admin')
        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        $hasAccess = in_array($user->role, $allowedRoles, true)
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($allowedRoles));

        if (! $hasAccess) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Halaman ini hanya untuk otoritas yang berwenang.');
        }

        return $next($request);
    }
}

