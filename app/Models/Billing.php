<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model Billing - Manajemen Tagihan & Kasir Rumah Sakit
 *
 * Terhubung dengan Pasien, Reservasi Rawat Jalan, Petugas Perawat/Kasir,
 * dan Rincian Tagihan (BillingItem). Mendukung Pembayaran Tunai & Xendit Gateway.
 *
 * @property int $billing_id
 * @property int $reservation_id
 * @property int $patient_id
 * @property int|null $processed_by_nurse_id
 * @property string $invoice_number
 * @property float $total_amount
 * @property string $status ('unpaid', 'pending', 'paid', 'expired', 'cancelled')
 * @property string|null $payment_method ('cash', 'xendit_invoice', 'xendit_qris')
 * @property string|null $xendit_id
 * @property string|null $xendit_payment_url
 * @property Carbon|null $paid_at
 * @property-read Patient|null $patient
 * @property-read Appointment|null $reservation
 * @property-read Appointment|null $appointment
 * @property-read Nurse|null $processedByNurse
 * @property-read Collection<int, BillingItem> $items
 */
class Billing extends Model
{
    use HasFactory;

    protected $table = 'billing';

    protected $primaryKey = 'billing_id';

    protected $fillable = [
        'reservation_id',
        'appointment_id',
        'patient_id',
        'processed_by_nurse_id',
        'invoice_number',
        'external_id',
        'total_amount',
        'amount',
        'paid_amount',
        'status',
        'payment_method',
        'xendit_id',
        'xendit_invoice_id',
        'xendit_payment_url',
        'invoice_url',
        'paid_at',
        'cashier_shift_id',
        'notes',
        'payment_details',
    ];

    protected function casts(): array
    {
        return [
            'billing_id' => 'integer',
            'reservation_id' => 'integer',
            'appointment_id' => 'integer',
            'patient_id' => 'integer',
            'processed_by_nurse_id' => 'integer',
            'total_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Pasien penerima tagihan.
     *
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * Relasi ke Reservasi / Janji Temu rawat jalan.
     *
     * @return BelongsTo<Appointment, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Alias relasi ke Appointment untuk fleksibilitas kode.
     *
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'reservation_id', 'appointment_id');
    }

    /**
     * Relasi ke Petugas Perawat / Kasir Tetap yang memproses pembayaran.
     *
     * @return BelongsTo<Nurse, $this>
     */
    public function processedByNurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'processed_by_nurse_id', 'nurse_id');
    }

    /**
     * Relasi ke item rincian tagihan (biaya dokter, obat, tindakan).
     *
     * @return HasMany<BillingItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillingItem::class, 'billing_id', 'billing_id');
    }

    /**
     * Alias accessor id ke billing_id.
     */
    public function getIdAttribute(): int
    {
        return (int) ($this->attributes['billing_id'] ?? 0);
    }

    /**
     * Helper untuk memeriksa status lunas.
     */
    public function isPaid(): bool
    {
        return in_array(strtoupper((string) $this->status), ['PAID', 'SETTLED']);
    }

    /**
     * Helper untuk memeriksa status belum bayar.
     */
    public function isUnpaid(): bool
    {
        return in_array(strtoupper((string) $this->status), ['UNPAID', 'PENDING']);
    }
}
