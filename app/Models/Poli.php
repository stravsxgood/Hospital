<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $poli_id
 * @property string $kode_poli
 * @property string $name_poli
 * @property string $location
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Doctor> $doctors
 * @property-read Collection<int, Room> $rooms
 * @property-read Collection<int, DoctorSchedule> $schedules
 * @property-read Collection<int, Registration> $registrations
 */
class Poli extends Model
{
    use HasFactory;

    protected $table = 'poli';

    protected $primaryKey = 'poli_id';

    protected $fillable = [
        'kode_poli',
        'name_poli',
        'location',
        'status',
    ];

    /**
     * @return BelongsToMany<Doctor, $this>
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_poli', 'poli_id', 'doctor_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Room, $this>
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_poli', 'poli_id', 'room_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<DoctorSchedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'poli_id', 'poli_id');
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'poli_id', 'poli_id');
    }
}
