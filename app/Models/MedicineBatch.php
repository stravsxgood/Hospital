<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MedicineBatch - Data Batch & Tanggal Kedaluwarsa Obat (FEFO Engine)
 *
 * @property int $medicine_batch_id
 * @property int $medicine_id
 * @property string $batch_number
 * @property \Carbon\Carbon $expiry_date
 * @property int $stock_quantity
 * @property float $purchase_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MedicineBatch extends Model
{
    use HasFactory;

    protected $table = 'medicine_batch';
    protected $primaryKey = 'medicine_batch_id';

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiry_date',
        'stock_quantity',
        'purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'medicine_batch_id' => 'integer',
            'medicine_id'       => 'integer',
            'stock_quantity'    => 'integer',
            'purchase_price'    => 'decimal:2',
            'expiry_date'       => 'date',
        ];
    }

    /**
     * Relasi ke Master Obat
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'medicine_id');
    }

    /**
     * Scope batch dengan stok tersedia (> 0)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope FEFO (Urutkan dari tanggal kedaluwarsa terdekat)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFefoOrder(Builder $query): Builder
    {
        return $query->orderBy('expiry_date', 'asc');
    }

    /**
     * Scope batch yang akan kedaluwarsa dalam X hari ke depan
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        $today = Carbon::today();
        $future = Carbon::today()->addDays($days);

        return $query->where('stock_quantity', '>', 0)
            ->whereBetween('expiry_date', [$today, $future]);
    }

    /**
     * Scope batch yang sudah kedaluwarsa
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->where('expiry_date', '<', Carbon::today());
    }
}
