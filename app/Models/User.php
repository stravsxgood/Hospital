<?php

namespace App\Models;

use App\Concerns\HasTeams;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Membership> $teamMemberships
 * @property-read Collection<int, Team> $teams
 * @property-read Patient|null $patient
 * @property-read Doctor|null $doctor
 * @property-read Nurse|null $nurse
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'last_login_at', 'email_verified_at', 'current_team_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles, HasTeams {
        HasTeams::teams insteadof HasRoles;
    }
    use Notifiable;
    use PasskeyAuthenticatable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Eloquent
    |--------------------------------------------------------------------------
    */

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'user_id', 'id');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id', 'id');
    }

    public function nurse(): HasOne
    {
        return $this->hasOne(Nurse::class, 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getRoleAttribute(): ?string
    {
        // 1. Cek role dari Spatie terlebih dahulu
        if ($this->roles()->exists()) {
            return $this->getRoleNames()->first();
        }

        // 2. Jika user memiliki relasi Doctor terdaftar -> role 'doctor'
        if ($this->relationLoaded('doctor') ? ($this->doctor !== null) : $this->doctor()->exists()) {
            return 'doctor';
        }

        // 3. Jika user memiliki relasi Nurse terdaftar -> role 'nurse'
        if ($this->relationLoaded('nurse') ? ($this->nurse !== null) : $this->nurse()->exists()) {
            return 'nurse';
        }

        // 4. Jika user memiliki relasi Patient terdaftar -> role 'patient'
        if ($this->relationLoaded('patient') ? ($this->patient !== null) : $this->patient()->exists()) {
            return 'patient';
        }

        // 5. Jika user memiliki role eksplisit di kolom/atribut role
        if (! empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        // 6. Jika user terikat dengan tim internal
        if ($this->currentTeam) {
            return $this->teamRole($this->currentTeam)?->value ?? 'staff';
        }

        return 'patient';
    }

    public function getIsDoctorAttribute(): bool
    {
        return $this->hasRole('dpjp-doctor') || $this->role === 'doctor';
    }

    public function getIsAdminAttribute(): bool
    {
        $rawRole = strtolower(trim((string) ($this->attributes['role'] ?? '')));
        $accessorRole = strtolower(trim((string) $this->role));
        $normalizedAccessor = str_replace(['-', '_'], ' ', $accessorRole);

        if (in_array($rawRole, ['admin', 'super-admin', 'super admin', 'super_admin', 'administrator'], true)) {
            return true;
        }

        if (in_array($normalizedAccessor, ['admin', 'super admin', 'administrator'], true)) {
            return true;
        }

        return $this->hasAnyRole(['super-admin', 'Super Admin', 'super_admin', 'super admin', 'admin', 'Admin', 'Administrator']);
    }
}
