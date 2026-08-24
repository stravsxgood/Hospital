<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecordAuditLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuditLogController extends Controller
{
    /**
     * Display Global Regulatory Access Audit Logs for Super Admin.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $action = $request->query('action');
        $search = $request->query('search');
        $perPage = (int) ($request->query('per_page', 20));

        $driver = DB::connection()->getDriverName();
        $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

        $query = MedicalRecordAuditLog::query()
            ->with([
                'user:id,name,email,role',
                'medicalRecord:medical_record_id,patient_id,doctor_id,assessment',
                'medicalRecord.patient:patient_id,name,resident_n',
                'medicalRecord.doctor:doctor_id,name',
            ])
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($search, function ($q) use ($search, $likeOp) {
                $term = "%{$search}%";
                $q->where(function ($sub) use ($term, $likeOp) {
                    $sub->where('ip_address', $likeOp, $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', $likeOp, $term)->orWhere('email', $likeOp, $term))
                        ->orWhereHas('medicalRecord.patient', fn ($p) => $p->where('name', $likeOp, $term)->orWhere('resident_n', $likeOp, $term));
                });
            })
            ->latest('audit_log_id');

        $logs = $query->paginate($perPage)->withQueryString();

        $today = Carbon::today()->toDateString();
        $stats = [
            'total_access' => MedicalRecordAuditLog::count(),
            'views_today' => MedicalRecordAuditLog::where('action', 'view')->whereDate('created_at', $today)->count(),
            'creates_today' => MedicalRecordAuditLog::where('action', 'create')->whereDate('created_at', $today)->count(),
            'exports_today' => MedicalRecordAuditLog::where('action', 'export_pdf')->whereDate('created_at', $today)->count(),
        ];

        $payload = [
            'logs' => $logs,
            'filters' => [
                'action' => $action,
                'search' => $search,
            ],
            'stats' => $stats,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Audit logs retrieved successfully.',
                'data' => $payload,
            ]);
        }

        return Inertia::render('admin/AuditLogs/Index', $payload);
    }
}
