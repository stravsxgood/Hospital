<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'registration_id',
        'payment_date',
        'payment_total',
        'payment_method',
        'payment_status',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id', 'registration_id');
    }
}
