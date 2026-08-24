<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MedicalRecordAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Service AuditLogService
 *
 * Mengelola pencatatan riwayat akses dan mutasi Rekam Medis Pasien (EMR)
 * guna memenuhi standar Kepatuhan UU Perlindungan Data Pribadi (UU PDP No. 27/2022)
 * dan Peraturan Menteri Kesehatan tentang Rekam Medis Elektronik (Permenkes No. 24/2022).
 */
class AuditLogService
{
    /**
     * Catat aksi akses rekam medis secara immutable.
     *
     * @param int $medicalRecordId ID Rekam Medis yang diakses/dimutasi
     * @param string $action Jenis tindakan ('view', 'create', 'update', 'export_pdf', 'print')
     * @param array|null $payloadDiff Perubahan data atau rincian aksi dalam format array/JSON
     * @param int|null $userId ID User yang mengakses (opsional, default Auth::id())
     * @return MedicalRecordAuditLog
     */
    public static function logAccess(
        int $medicalRecordId,
        string $action = 'view',
        ?array $payloadDiff = null,
        ?int $userId = null
    ): MedicalRecordAuditLog {
        $resolvedUserId = $userId ?? Auth::id();

        // Jika tidak ada user terautentikasi (misal via webhook/sistem background), gunakan ID 1 atau fallback
        if (! $resolvedUserId) {
            $resolvedUserId = 1;
        }

        return MedicalRecordAuditLog::create([
            'medical_record_id' => $medicalRecordId,
            'user_id'           => $resolvedUserId,
            'action'            => $action,
            'ip_address'        => Request::ip(),
            'user_agent'        => substr((string) Request::userAgent(), 0, 500),
            'payload_diff'      => $payloadDiff,
            'created_at'        => now(),
        ]);
    }
}
