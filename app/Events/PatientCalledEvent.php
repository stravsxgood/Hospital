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
 * Event PatientCalledEvent - Dipicu saat dokter memanggil nomor antrean pasien
 * Mengirimkan data pemanggilan secara real-time ke Layar TV Display Antrean Publik
 * dan Konsol Antrean Dokter.
 */
class PatientCalledEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $queueNumber,
        public readonly string $patientName,
        public readonly string $poliName,
        public readonly string $roomName,
        public readonly string $doctorName,
        public readonly int $doctorId,
        public readonly string $voiceText,
        public readonly string $calledAt
    ) {}

    /**
     * Tentukan channel siaran WebSocket
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('queue-display'),
            new PrivateChannel('doctor.'.$this->doctorId),
        ];
    }

    /**
     * Nama event broadcast
     */
    public function broadcastAs(): string
    {
        return 'PatientCalledEvent';
    }

    /**
     * Payload data siaran
     */
    public function broadcastWith(): array
    {
        return [
            'appointment_id' => $this->appointmentId,
            'queue_number' => $this->queueNumber,
            'patient_name' => $this->patientName,
            'poli_name' => $this->poliName,
            'room_name' => $this->roomName,
            'doctor_name' => $this->doctorName,
            'doctor_id' => $this->doctorId,
            'voice_text' => $this->voiceText,
            'called_at' => $this->calledAt,
        ];
    }
}
