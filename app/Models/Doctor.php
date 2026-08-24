<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctor';
    protected $primaryKey = 'doctor_id';

    protected $fillable = [
        'user_id',
        'specialization_id',
        'name',
        'sip_number',
        'gender',
        'number_phone',
        'email',
        'alamat',
        'join_date',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_id', 'specialization_id');
    }

    public function polis(): BelongsToMany
    {
        return $this->belongsToMany(Poli::class, 'doctor_poli', 'doctor_id', 'poli_id')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'doctor_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'doctor_id', 'doctor_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'doctor_id', 'doctor_id');
    }

    public function soapTemplates(): HasMany
    {
        return $this->hasMany(SoapTemplate::class, 'doctor_id', 'doctor_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id', 'doctor_id');
    }

    public function supervisedLogbooks(): HasMany
    {
        return $this->hasMany(ClinicalLogbook::class, 'doctor_id', 'doctor_id');
    }
}