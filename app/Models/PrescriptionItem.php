<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PrescriptionItem - Rincian Item Resep Obat
 * Menyimpan data kuantitas, dosis/aturan pakai (signa), petunjuk konsumsi,
 * dan terhubung ke resep utama serta master obat.
 *
 * @property int $prescription_item_id
 * @property int $prescription_id
 * @property int $medicine_id
 * @property int $quantity
 * @property string $dosage
 * @property string $instructions
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PrescriptionItem extends Model
{
    use HasFactory;

    protected $table = 'prescription_item';
    protected $primaryKey = 'prescription_item_id';
    protected $guarded = [];

    /**
     * PostgreSQL type safety casting
     */
    protected function casts(): array
    {
        return [
            'prescription_item_id' => 'integer',
            'prescription_id'      => 'integer',
            'medicine_id'          => 'integer',
            'quantity'             => 'integer',
        ];
    }

    /**
     * Relasi ke Resep Obat Utama
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'prescription_id');
    }

    /**
     * Relasi ke Master Data Obat
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'medicine_id');
    }
}
