<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model Prescription - Resep Obat Elektronik (E-Prescription)
 * Menampung nomor resep unik, status penyiapan farmasi, catatan resep,
 * dan terhubung ke rekam medis serta rincian item obat yang diresepkan.
 *
 * @property int $prescription_id
 * @property int $medical_record_id
 * @property string $prescription_number
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MedicalRecord|null $medicalRecord
 * @property-read Collection<int, PrescriptionItem> $items
 * @property-read Collection<int, PrescriptionItem> $prescriptionItems
 */
class Prescription extends Model
{
    use HasFactory;

    protected $table = 'prescription';

    protected $primaryKey = 'prescription_id';

    protected $guarded = [];

    /**
     * PostgreSQL type safety casting
     */
    protected function casts(): array
    {
        return [
            'prescription_id' => 'integer',
            'medical_record_id' => 'integer',
        ];
    }

    /**
     * Relasi ke Rekam Medis (EMR)
     *
     * @return BelongsTo<MedicalRecord, $this>
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Relasi ke rincian item obat resep
     *
     * @return HasMany<PrescriptionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id', 'prescription_id');
    }

    /**
     * Alias relasi ke PrescriptionItem
     */
    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id', 'prescription_id');
    }

    /**
     * Scope resep dengan status menunggu
     */
    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', 'menunggu');
    }

    /**
     * Scope resep dengan status sedang diproses / diracik
     */
    public function scopeDiproses(Builder $query): Builder
    {
        return $query->where('status', 'diproses');
    }

    /**
     * Scope resep dengan status selesai
     */
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Scope resep aktif farmasi (menunggu atau sedang diproses)
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['menunggu', 'diproses']);
    }
}
