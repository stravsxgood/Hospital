<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'registration';

    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'poli_id',
        'verified_by_nurse_id',
        'registration_date',
        'queue_number',
        'status',
        'complaint',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'verified_by_nurse_id', 'nurse_id');
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class, 'registration_id', 'registration_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'registration_id', 'registration_id');
    }

    public function scopeByPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }
}
