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
 * Event PaymentSettledEvent - Dipicu saat pembayaran tagihan kasir/QRIS lunas
 * Menyiarkan konfirmasi instan sub-detik ke modal kasir (PaymentModal.vue)
 * dan panel antrean pasien tanpa perlu polling berulang.
 */
class PaymentSettledEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $billingId,
        public readonly string $invoiceNumber,
        public readonly string $status,
        public readonly string $paymentMethod,
        public readonly float|int $paidAmount,
        public readonly string $paidAt
    ) {}

    /**
     * Tentukan channel siaran WebSocket
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('billing.'.$this->billingId),
        ];
    }

    /**
     * Nama event broadcast
     */
    public function broadcastAs(): string
    {
        return 'PaymentSettledEvent';
    }

    /**
     * Payload data siaran
     */
    public function broadcastWith(): array
    {
        return [
            'billing_id' => $this->billingId,
            'invoice_number' => $this->invoiceNumber,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'paid_amount' => $this->paidAmount,
            'paid_at' => $this->paidAt,
        ];
    }
}
