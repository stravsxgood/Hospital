<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model PatientAllergy - Catatan Riwayat Alergi Pasien
 *
 * @property int $patient_allergy_id
 * @property int $patient_id
 * @property string $allergen_type
 * @property string $allergen_name
 * @property string $severity
 * @property string|null $reaction
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PatientAllergy extends Model
{
    use HasFactory;

    protected $table = 'patient_allergy';

    protected $primaryKey = 'patient_allergy_id';

    protected $fillable = [
        'patient_id',
        'allergen_type',
        'allergen_name',
        'severity',
        'reaction',
    ];

    protected function casts(): array
    {
        return [
            'patient_allergy_id' => 'integer',
            'patient_id' => 'integer',
        ];
    }

    /**
     * Relasi ke Pasien
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * Scope khusus alergi obat
     */
    public function scopeMedicine(Builder $query): Builder
    {
        return $query->where('allergen_type', 'medicine');
    }
}
