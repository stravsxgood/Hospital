<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $doctor_id
 * @property int $user_id
 * @property int $specialization_id
 * @property string $name
 * @property string $sip_number
 * @property string $gender
 * @property string|null $number_phone
 * @property string|null $email
 * @property string|null $alamat
 * @property string $join_date
 * @property string $status
 */
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
