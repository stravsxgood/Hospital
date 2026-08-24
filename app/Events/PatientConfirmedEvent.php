<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event PatientConfirmedEvent - Dipicu saat meja depan (front-office) memverifikasi kedatangan pasien
 * Menyiarkan pembaruan langsung ke konsol dokter DPJP yang bersangkutan agar nama pasien
 * langsung masuk ke antrean aktif tanpa reload.
 */
class PatientConfirmedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $reservationId,
        public readonly string $queueNumber,
        public readonly string $patientName,
        public readonly int $doctorId,
        public readonly string $status,
        public readonly string $confirmedAt
    ) {}

    /**
     * Tentukan channel siaran WebSocket
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('doctor.' . $this->doctorId),
        ];
    }

    /**
     * Nama event broadcast
     */
    public function broadcastAs(): string
    {
        return 'PatientConfirmedEvent';
    }

    /**
     * Payload data siaran
     */
    public function broadcastWith(): array
    {
        return [
            'reservation_id' => $this->reservationId,
            'queue_number'   => $this->queueNumber,
            'patient_name'   => $this->patientName,
            'doctor_id'      => $this->doctorId,
            'status'         => $this->status,
            'confirmed_at'   => $this->confirmedAt,
        ];
    }
}
