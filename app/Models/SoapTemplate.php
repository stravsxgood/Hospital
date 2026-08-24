<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model SoapTemplate - Template Cepat SOAP Notes Dokter
 *
 * @property int $soap_template_id
 * @property int|null $doctor_id
 * @property string $template_name
 * @property string|null $subjective_template
 * @property array|null $objective_template
 * @property string|null $assessment_template
 * @property string|null $plan_template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SoapTemplate extends Model
{
    use HasFactory;

    protected $table = 'soap_template';
    protected $primaryKey = 'soap_template_id';

    protected $fillable = [
        'doctor_id',
        'template_name',
        'subjective_template',
        'objective_template',
        'assessment_template',
        'plan_template',
    ];

    protected function casts(): array
    {
        return [
            'soap_template_id'   => 'integer',
            'doctor_id'          => 'integer',
            'objective_template' => 'array',
        ];
    }

    /**
     * Relasi ke Dokter pemilik template (null jika template default sistem)
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Scope untuk mengambil template milik dokter tertentu ditambah template umum sistem
     *
     * @param Builder $query
     * @param int|null $doctorId
     * @return Builder
     */
    public function scopeForDoctor(Builder $query, ?int $doctorId): Builder
    {
        return $query->where(function (Builder $q) use ($doctorId) {
            $q->whereNull('doctor_id');
            if ($doctorId) {
                $q->orWhere('doctor_id', $doctorId);
            }
        });
    }
}
