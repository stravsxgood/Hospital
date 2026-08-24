<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model MedicalRecord - Rekam Medis Elektronik (EMR)
 * Menyimpan catatan SOAP (Subjective, Objective / Vital Signs, Assessment, Plan)
 * dan terhubung dengan pasien, dokter pemeriksa, reservasi/jadwal, serta resep obat.
 *
 * @property int $medical_record_id
 * @property int|null $reservation_id
 * @property int $patient_id
 * @property int $doctor_id
 * @property string $subjective
 * @property array $objective
 * @property string $assessment
 * @property string $plan
 * @property string|null $physical_check
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_record';

    protected $primaryKey = 'medical_record_id';

    protected $guarded = [];

    /**
     * PostgreSQL type safety casting untuk JSON & foreign keys
     */
    protected function casts(): array
    {
        return [
            'medical_record_id' => 'integer',
            'reservation_id' => 'integer',
            'patient_id' => 'integer',
            'doctor_id' => 'integer',
            'objective' => 'array',
        ];
    }

    /**
     * Relasi ke data Pasien
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * Relasi ke Dokter yang memeriksa
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Relasi ke data Reservasi / Appointment antrean
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Alias relasi ke Appointment
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Relasi ke Resep Obat Elektronik (E-Prescription)
     */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Relasi ke log audit akses UU PDP
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(MedicalRecordAuditLog::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Relasi ke logbook klinis koas terkait
     */
    public function clinicalLogbooks(): HasMany
    {
        return $this->hasMany(ClinicalLogbook::class, 'medical_record_id', 'medical_record_id');
    }
}
