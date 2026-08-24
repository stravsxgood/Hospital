<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model MedicalRecordAuditLog - Log Audit Akses Rekam Medis (Kepatuhan UU PDP & Hukum Kesehatan)
 *
 * @property int $audit_log_id
 * @property int $medical_record_id
 * @property int $user_id
 * @property string $action ('view', 'create', 'update', 'export_pdf', 'print')
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $payload_diff
 * @property \Carbon\Carbon $created_at
 */
class MedicalRecordAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false; // Hanya menggunakan created_at yang immutable

    protected $table = 'medical_record_audit_log';
    protected $primaryKey = 'audit_log_id';

    protected $fillable = [
        'medical_record_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'payload_diff',
        'created_at',
    ];

    /**
     * PostgreSQL type safety casts
     */
    protected function casts(): array
    {
        return [
            'audit_log_id'      => 'integer',
            'medical_record_id' => 'integer',
            'user_id'           => 'integer',
            'payload_diff'      => 'array',
            'created_at'        => 'datetime',
        ];
    }

    /**
     * Relasi ke Rekam Medis yang diakses
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id', 'medical_record_id');
    }

    /**
     * Relasi ke User / Tenaga Medis yang melakukan akses
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Scope urutan log terbaru
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope filter berdasarkan jenis tindakan audit
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }
}
