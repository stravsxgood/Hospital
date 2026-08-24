<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ClinicalLogbook - Logbook Kasus Klinis Mahasiswa Magang / Dokter Muda (Koas)
 *
 * @property int $clinical_logbook_id
 * @property int $nurse_id (Mahasiswa Koas / Perawat Pembuat)
 * @property int $patient_id
 * @property int|null $medical_record_id
 * @property int $doctor_id (DPJP Pembimbing / Supervisor)
 * @property string $activity_type ('anamnesis', 'physical_exam', 'procedure_assistance', 'case_discussion')
 * @property string $case_title
 * @property string $clinical_findings
 * @property string|null $procedure_performed
 * @property string $learning_reflection
 * @property string|null $supervisor_feedback
 * @property int|null $score
 * @property string $status ('draft', 'submitted', 'approved', 'revision_needed')
 * @property \Carbon\Carbon|null $submitted_at
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ClinicalLogbook extends Model
{
    use HasFactory;

    protected $table = 'clinical_logbook';
    protected $primaryKey = 'clinical_logbook_id';

    protected $fillable = [
        'nurse_id',
        'patient_id',
        'medical_record_id',
        'doctor_id',
        'activity_type',
        'case_title',
        'clinical_findings',
        'procedure_performed',
        'learning_reflection',
        'supervisor_feedback',
        'score',
        'status',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * PostgreSQL type safety casts
     */
    protected function casts(): array
    {
        return [
            'clinical_logbook_id' => 'integer',
            'nurse_id'            => 'integer',
            'patient_id'          => 'integer',
            'medical_record_id'   => 'integer',
            'doctor_id'           => 'integer',
            'score'               => 'integer',
            'submitted_at'        => 'datetime',
            'reviewed_at'         => 'datetime',
        ];
    }

    /**
     * Relasi ke Koas / Mahasiswa pembuat logbook (di tabel nurse)
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'nurse_id');
    }

    /**
     * Relasi ke data Pasien
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * Relasi ke Rekam Medis (EMR) terkait (opsional)
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Relasi ke Dokter DPJP Supervisor / Pembimbing
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Scope filter logbook milik Koas tertentu
     */
    public function scopeForKoas(Builder $query, int $nurseId): Builder
    {
        return $query->where('nurse_id', $nurseId);
    }

    /**
     * Scope filter logbook yang harus disupervisi oleh DPJP tertentu
     */
    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope filter logbook yang masih menunggu verifikasi / review
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope filter logbook yang telah disetujui DPJP
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
