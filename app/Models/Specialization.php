<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    use HasFactory;

    // Tentukan nama tabel di database PostgreSQL
    protected $table = 'specialization';

    // Tentukan primary key kustom
    protected $primaryKey = 'specialization_id';

    protected $fillable = [
        'code_specialization',
        'name_specialization',
        'description',
    ];

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'specialization_id', 'specialization_id');
    }
}
