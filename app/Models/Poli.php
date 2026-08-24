<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_poli', 'poli_id', 'doctor_id')
            ->withTimestamps();
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_poli', 'poli_id', 'room_id')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'poli_id', 'poli_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'poli_id', 'poli_id');
    }
}
