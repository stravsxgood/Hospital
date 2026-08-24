<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Medicine - Master Data Obat & Farmasi
 * Menyimpan katalog obat, sediaan/tipe, stok tersedia, satuan, dan harga.
 *
 * @property int $medicine_id
 * @property string $code_medicine
 * @property string $name_medicine
 * @property string $type
 * @property int $stock
 * @property string $unit
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicine';
    protected $primaryKey = 'medicine_id';
    protected $guarded = [];

    /**
     * PostgreSQL type safety casting
     */
    protected function casts(): array
    {
        return [
            'medicine_id' => 'integer',
            'stock'       => 'integer',
            'price'       => 'decimal:2',
        ];
    }

    /**
     * Relasi ke baris item resep obat
     */
    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id', 'medicine_id');
    }

    /**
     * Relasi ke batch & tanggal kedaluwarsa obat (FEFO)
     */
    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id', 'medicine_id');
    }

    /**
     * Scope obat habis (stok 0)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope obat stok menipis (1 s/d threshold)
     *
     * @param Builder $query
     * @param int $threshold
     * @return Builder
     */
    public function scopeLowStock(Builder $query, int $threshold = 10): Builder
    {
        return $query->where('stock', '>', 0)->where('stock', '<=', $threshold);
    }

    /**
     * Scope obat tersedia (stok > 0)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }
}

