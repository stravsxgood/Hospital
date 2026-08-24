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
 * Event PrescriptionCreatedEvent - Dipicu saat dokter menerbitkan resep elektronik baru
 * Menyiarkan notifikasi instan ke antrean farmasi/apotek tanpa reload halaman.
 */
class PrescriptionCreatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $prescriptionId,
        public readonly string $prescriptionNumber,
        public readonly string $patientName,
        public readonly string $doctorName,
        public readonly string $poliName,
        public readonly int $totalItems,
        public readonly string $createdAt
    ) {}

    /**
     * Tentukan channel siaran WebSocket
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pharmacy'),
        ];
    }

    /**
     * Nama event broadcast
     */
    public function broadcastAs(): string
    {
        return 'PrescriptionCreatedEvent';
    }

    /**
     * Payload data siaran
     */
    public function broadcastWith(): array
    {
        return [
            'prescription_id' => $this->prescriptionId,
            'prescription_number' => $this->prescriptionNumber,
            'patient_name' => $this->patientName,
            'doctor_name' => $this->doctorName,
            'poli_name' => $this->poliName,
            'total_items' => $this->totalItems,
            'created_at' => $this->createdAt,
        ];
    }
}
