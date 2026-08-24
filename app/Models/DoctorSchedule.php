<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $table = 'doctor_schedule';
    protected $primaryKey = 'doctor_schedule_id';

    protected $fillable = [
        'doctor_id',
        'poli_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
        'quota_day',
        'status',
    ];

    protected $casts = [
        'doctor_schedule_id' => 'integer',
        'doctor_id' => 'integer',
        'poli_id' => 'integer',
        'room_id' => 'integer',
        'quota_day' => 'integer',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}