<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model BillingItem - Rincian Item Tagihan Medis
 *
 * @property int $billing_item_id
 * @property int $billing_id
 * @property string $item_type ('consultation_fee', 'medicine', 'procedure')
 * @property string $item_name
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 */
class BillingItem extends Model
{
    use HasFactory;

    protected $table = 'billing_item';
    protected $primaryKey = 'billing_item_id';
    protected $guarded = ['billing_item_id'];

    protected function casts(): array
    {
        return [
            'billing_item_id' => 'integer',
            'billing_id'      => 'integer',
            'quantity'        => 'integer',
            'unit_price'      => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Header Billing utama.
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'billing_id', 'billing_id');
    }
}
