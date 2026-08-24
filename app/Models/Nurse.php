<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Nurse - Tenaga Kesehatan Staf / Perawat / Koas
 *
 * @property int $nurse_id
 * @property int $user_id
 * @property string $name
 * @property string $registration_number
 * @property string $type ('tetap', 'koas')
 * @property string|null $institute
 * @property string|null $gender
 * @property string|null $date_start
 * @property string|null $date_end
 */
class Nurse extends Model
{
    use HasFactory;

    protected $table = 'nurse';
    protected $primaryKey = 'nurse_id';

    protected $fillable = [
        'user_id',
        'name',
        'registration_number',
        'type',
        'institute',
        'gender',
        'date_start',
        'date_end',
    ];

    protected function casts(): array
    {
        return [
            'nurse_id'   => 'integer',
            'user_id'    => 'integer',
            'date_start' => 'date',
            'date_end'   => 'date',
        ];
    }

    /**
     * Memeriksa apakah status perawat adalah Staf Tetap (Pekerja).
     */
    public function isTetap(): bool
    {
        return strtolower(trim((string) $this->type)) === 'tetap';
    }

    /**
     * Memeriksa apakah status perawat adalah Mahasiswa Magang / Koas.
     */
    public function isKoas(): bool
    {
        return strtolower(trim((string) $this->type)) === 'koas';
    }

    /**
     * Relasi ke akun User autentikasi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke pendaftaran pasien yang diverifikasi oleh perawat.
     */
    public function verifiedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'verified_by_nurse_id', 'nurse_id');
    }

    /**
     * Relasi ke tagihan/billing kasir yang diproses oleh perawat tetap.
     */
    public function processedBillings(): HasMany
    {
        return $this->hasMany(Billing::class, 'processed_by_nurse_id', 'nurse_id');
    }

    /**
     * Relasi ke sesi shift kasir
     */
    public function cashierShifts(): HasMany
    {
        return $this->hasMany(CashierShift::class, 'nurse_id', 'nurse_id');
    }

    /**
     * Ambil sesi shift kasir yang sedang aktif terbuka
     */
    public function currentOpenShift(): ?CashierShift
    {
        return $this->cashierShifts()->where('status', 'open')->latest('opened_at')->first();
    }

    /**
     * Relasi ke logbook klinis koas
     */
    public function clinicalLogbooks(): HasMany
    {
        return $this->hasMany(ClinicalLogbook::class, 'nurse_id', 'nurse_id');
    }
}