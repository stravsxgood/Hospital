<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $table = 'room';

    protected $primaryKey = 'room_id';

    protected $fillable = [
        'code_room',
        'name_room',
        'type_room',
        'capacity',
        'floor',
    ];

    public function polis(): BelongsToMany
    {
        return $this->belongsToMany(Poli::class, 'room_poli', 'room_id', 'poli_id')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'room_id', 'room_id');
    }
}
