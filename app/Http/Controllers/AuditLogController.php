<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MedicalRecordAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller AuditLogController
 *
 * Menampilkan catatan jejak audit akses rekam medis pasien sesuai standar
 * Kepatuhan UU Perlindungan Data Pribadi (UU PDP No. 27/2022) & Permenkes EMR.
 */
class AuditLogController extends Controller
{
    /**
     * Tampilkan riwayat log audit akses rekam medis.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $query = MedicalRecordAuditLog::with([
            'user',
            'medicalRecord.patient',
            'medicalRecord.doctor',
        ]);

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('medicalRecord.patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('resident_n', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate((int) $request->query('per_page', 20))
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'data' => $logs,
            ]);
        }

        return Inertia::render('staff/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'action' => $request->query('action', ''),
                'search' => $request->query('search', ''),
            ],
            'stats' => [
                'total_access' => MedicalRecordAuditLog::count(),
                'views_today' => MedicalRecordAuditLog::where('action', 'view')->whereDate('created_at', today())->count(),
                'creates_today' => MedicalRecordAuditLog::where('action', 'create')->whereDate('created_at', today())->count(),
                'exports_today' => MedicalRecordAuditLog::where('action', 'export_pdf')->whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
