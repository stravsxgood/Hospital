<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CashierShift - Rekonsiliasi Kasir & Tutup Shift
 *
 * @property int $cashier_shift_id
 * @property int $nurse_id
 * @property string $shift_name
 * @property \Carbon\Carbon $opened_at
 * @property \Carbon\Carbon|null $closed_at
 * @property float $opening_cash
 * @property float|null $closing_cash_actual
 * @property float $total_cash_system
 * @property float $total_qris_system
 * @property float|null $discrepancy
 * @property string|null $notes
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CashierShift extends Model
{
    use HasFactory;

    protected $table = 'cashier_shift';
    protected $primaryKey = 'cashier_shift_id';

    protected $fillable = [
        'nurse_id',
        'shift_name',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash_actual',
        'total_cash_system',
        'total_qris_system',
        'discrepancy',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cashier_shift_id'    => 'integer',
            'nurse_id'            => 'integer',
            'opened_at'           => 'datetime',
            'closed_at'           => 'datetime',
            'opening_cash'        => 'decimal:2',
            'closing_cash_actual' => 'decimal:2',
            'total_cash_system'   => 'decimal:2',
            'total_qris_system'   => 'decimal:2',
            'discrepancy'         => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Petugas Kasir / Perawat Tetap
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'nurse_id');
    }

    /**
     * Scope shift yang masih aktif terbuka
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope shift yang sudah ditutup
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }
}
