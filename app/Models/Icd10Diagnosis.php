<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Icd10Diagnosis - Master Data Standar Diagnosis ICD-10 WHO
 *
 * @property int $icd10_diagnosis_id
 * @property string $code
 * @property string $name_id
 * @property string|null $name_en
 * @property bool $is_common
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Icd10Diagnosis extends Model
{
    use HasFactory;

    protected $table = 'icd10_diagnosis';
    protected $primaryKey = 'icd10_diagnosis_id';

    protected $fillable = [
        'code',
        'name_id',
        'name_en',
        'is_common',
    ];

    protected function casts(): array
    {
        return [
            'icd10_diagnosis_id' => 'integer',
            'is_common'          => 'boolean',
        ];
    }

    /**
     * Scope pencarian kode atau nama diagnosis ICD-10
     *
     * @param Builder $query
     * @param string $term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $driver = $query->getConnection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($term, $likeOp) {
            $q->where('code', $likeOp, "{$term}%")
              ->orWhere('name_id', $likeOp, "%{$term}%")
              ->orWhere('name_en', $likeOp, "%{$term}%");
        });
    }

    /**
     * Scope hanya diagnosis yang sering digunakan
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCommon(Builder $query): Builder
    {
        return $query->where('is_common', true);
    }
}
