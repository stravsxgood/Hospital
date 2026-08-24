<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Reservation - Reservasi / Antrean Pasien Rawat Jalan
 * Terhubung dengan Pasien, Jadwal Dokter, dan Rekam Medis (EMR).
 */
class Reservation extends Model
{
    use HasFactory;

    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    protected $guarded = ['appointment_id'];

    protected function casts(): array
    {
        return [
            'appointment_id'     => 'integer',
            'patient_id'         => 'integer',
            'doctor_schedule_id' => 'integer',
            'appointment_date'   => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctorSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id', 'doctor_schedule_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'reservation_id', 'appointment_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'reservation_id', 'appointment_id');
    }

    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class, 'reservation_id', 'appointment_id');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Scope antrean berstatus pending (menunggu check-in meja depan).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope antrean berstatus confirmed (sudah hadir & diverifikasi).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope antrean yang sedang diperiksa oleh dokter di ruang praktik.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope antrean dengan status completed (pemeriksaan telah selesai).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope antrean dengan jadwal hari ini.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', today());
    }
}

