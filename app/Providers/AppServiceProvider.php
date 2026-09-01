<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());

        // RBAC Gate: Khusus Staf / Perawat Tetap (Pekerja)
        // Mahasiswa Magang / Koas (type: 'koas') dilarang mengakses kasir, billing, dan manajemen obat
        Gate::define('access-pekerja-only', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            if ($user->role === 'admin') {
                return true;
            }

            // Jika user adalah perawat tetap
            return $user->nurse !== null && $user->nurse->isTetap();
        });

        Gate::before(function ($user, $ability) {
            return $user instanceof User && $user->is_admin ? true : null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
