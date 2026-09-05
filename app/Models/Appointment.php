<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $appointment_id
 * @property int $patient_id
 * @property int $doctor_schedule_id
 * @property Carbon $appointment_date
 * @property string $queue_number
 * @property string|null $complaint
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Patient|null $patient
 * @property-read DoctorSchedule|null $doctorSchedule
 * @property-read MedicalRecord|null $medicalRecord
 * @property-read Collection<int, MedicalRecord> $medicalRecords
 * @property-read Billing|null $billing
 * @property-read Collection<int, Billing> $billings
 */
class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';

    protected $guarded = ['appointment_id'];

    protected $fillable = [
        'patient_id',
        'doctor_schedule_id',
        'appointment_date',
        'queue_number',
        'complaint',
        'status',
    ];

    public function getRouteKeyName(): string
    {
        return 'appointment_id';
    }

    protected function casts(): array
    {
        return [
            'appointment_id' => 'integer',
            'patient_id' => 'integer',
            'doctor_schedule_id' => 'integer',
            'appointment_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * @return BelongsTo<DoctorSchedule, $this>
     */
    public function doctorSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id', 'doctor_schedule_id');
    }

    /**
     * @return HasOne<MedicalRecord, $this>
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'reservation_id', 'appointment_id');
    }

    /**
     * @return HasMany<MedicalRecord, $this>
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'reservation_id', 'appointment_id');
    }

    /**
     * @return HasOne<Billing, $this>
     */
    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class, 'reservation_id', 'appointment_id');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Scope antrean dengan status pending (menunggu check-in di meja depan).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope antrean dengan status confirmed (telah hadir & siap diperiksa dokter).
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope antrean yang sedang dalam proses pemeriksaan dokter.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope antrean dengan status completed (pemeriksaan selesai).
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope antrean dengan tanggal kunjungan hari ini.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', today());
    }
}
