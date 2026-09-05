<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $doctor_schedule_id
 * @property int $doctor_id
 * @property int $poli_id
 * @property int $room_id
 * @property string $day
 * @property string $start_time
 * @property string $end_time
 * @property int $quota_day
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Doctor|null $doctor
 * @property-read Poli|null $poli
 * @property-read Room|null $room
 */
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

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /**
     * @return BelongsTo<Poli, $this>
     */
    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id', 'poli_id');
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}
