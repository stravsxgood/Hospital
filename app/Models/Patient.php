<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $patient_id
 * @property int $user_id
 * @property string $resident_n
 * @property string $name
 * @property string $gender
 * @property string $birthday_date
 * @property string|null $address
 * @property string|null $number_phone
 * @property string|null $disease
 * @property string $registration_date
 * @property string $status
 */
class Patient extends Model
{
    use HasFactory;

    protected $table = 'patient';

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'user_id',
        'resident_n',
        'name',
        'gender',
        'birthday_date',
        'address',
        'number_phone',
        'disease',
        'registration_date',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'patient_id', 'patient_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'patient_id');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class, 'patient_id', 'patient_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id', 'patient_id');
    }
}
