<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CloseCashierShiftRequest;
use App\Http\Requests\OpenCashierShiftRequest;
use App\Models\Billing;
use App\Models\CashierShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller CashierShiftController
 *
 * Mengelola siklus shift kasir perawat tetap:
 * - Pembukaan shift & pencatatan kas modal awal (opening cash float)
 * - Monitoring pendapatan real-time per shift
 * - Penutupan shift & rekonsiliasi selisih kas fisik vs sistem (discrepancy)
 * - Cetak ringkasan shift kasir
 */
class CashierShiftController extends Controller
{
    /**
     * Ambil informasi shift yang sedang aktif beserta statistik live transaksi kasir
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function currentShift(Request $request): JsonResponse
    {
        $nurse = $request->user()?->nurse;

        if (!$nurse || !$nurse->isTetap()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak. Fitur shift kasir hanya untuk perawat staf tetap.',
            ], 403);
        }

        $shift = $nurse->currentOpenShift();

        if (!$shift) {
            return response()->json([
                'status'    => true,
                'has_shift' => false,
                'message'   => 'Belum ada shift kasir yang aktif.',
                'data'      => null,
            ]);
        }

        // Hitung akumulasi penerimaan transaksi selama window shift ini
        $now = Carbon::now();
        $billings = Billing::query()
            ->where('processed_by_nurse_id', $nurse->nurse_id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$shift->opened_at, $now])
            ->get();

        $totalCash = (float) $billings->where('payment_method', 'cash')->sum('final_amount');
        $totalQris = (float) $billings->where('payment_method', 'qris')->sum('final_amount');
        $transactionCount = $billings->count();

        $expectedCash = (float) $shift->opening_cash + $totalCash;

        return response()->json([
            'status'    => true,
            'has_shift' => true,
            'message'   => 'Shift kasir aktif ditemukan.',
            'data'      => [
                'shift'             => $shift,
                'live_stats'        => [
                    'total_cash'        => $totalCash,
                    'total_qris'        => $totalQris,
                    'total_revenue'     => $totalCash + $totalQris,
                    'expected_cash'     => $expectedCash,
                    'transaction_count' => $transactionCount,
                ],
            ],
        ]);
    }

    /**
     * Buka sesi shift kasir baru
     *
     * @param OpenCashierShiftRequest $request
     * @return JsonResponse
     */
    public function openShift(OpenCashierShiftRequest $request): JsonResponse
    {
        $nurse = $request->user()->nurse;

        $existing = $nurse->currentOpenShift();
        if ($existing) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda masih memiliki shift yang sedang aktif. Tutup shift sebelumnya terlebih dahulu.',
            ], 422);
        }

        $shift = CashierShift::create([
            'nurse_id'            => $nurse->nurse_id,
            'shift_name'          => $request->validated('shift_name'),
            'opened_at'           => Carbon::now(),
            'opening_cash'        => (float) $request->validated('opening_cash'),
            'total_cash_system'   => 0.00,
            'total_qris_system'   => 0.00,
            'status'              => 'open',
            'notes'               => $request->validated('notes'),
        ]);

        return response()->json([
            'status'  => true,
            'message' => "Shift {$shift->shift_name} berhasil dibuka dengan kas awal Rp " . number_format((float) $shift->opening_cash, 0, ',', '.'),
            'data'    => $shift,
        ], 201);
    }

    /**
     * Tutup sesi shift kasir & jalankan rekonsiliasi kas sistem vs fisik
     *
     * @param CloseCashierShiftRequest $request
     * @return JsonResponse
     */
    public function closeShift(CloseCashierShiftRequest $request): JsonResponse
    {
        $nurse = $request->user()->nurse;
        $shift = $nurse->currentOpenShift();

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ditemukan shift aktif untuk ditutup.',
            ], 404);
        }

        $closedAt = Carbon::now();
        $closingCashActual = (float) $request->validated('closing_cash_actual');

        // Hitung total transaksi sistem selama rentang shift
        $billings = Billing::query()
            ->where('processed_by_nurse_id', $nurse->nurse_id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$shift->opened_at, $closedAt])
            ->get();

        $totalCash = (float) $billings->where('payment_method', 'cash')->sum('final_amount');
        $totalQris = (float) $billings->where('payment_method', 'qris')->sum('final_amount');
        
        $expectedCash = (float) $shift->opening_cash + $totalCash;
        $discrepancy = $closingCashActual - $expectedCash;

        DB::transaction(function () use ($shift, $closedAt, $closingCashActual, $totalCash, $totalQris, $discrepancy, $request) {
            $shift->update([
                'closed_at'           => $closedAt,
                'closing_cash_actual' => $closingCashActual,
                'total_cash_system'   => $totalCash,
                'total_qris_system'   => $totalQris,
                'discrepancy'         => $discrepancy,
                'status'              => 'closed',
                'notes'               => $request->validated('notes') ?? $shift->notes,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Shift kasir berhasil ditutup dan direkonsiliasi.',
            'data'    => [
                'shift'             => $shift->fresh(),
                'expected_cash'     => $expectedCash,
                'discrepancy'       => $discrepancy,
                'transaction_count' => $billings->count(),
            ],
        ]);
    }

    /**
     * Ambil data ringkasan rekonsiliasi shift untuk cetak kuitansi thermal POS
     *
     * @param int $id ID CashierShift
     * @param Request $request
     * @return JsonResponse
     */
    public function printSummary(int $id, Request $request): JsonResponse
    {
        $nurse = $request->user()?->nurse;
        if (!$nurse || !$nurse->isTetap()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $shift = CashierShift::with('nurse.user')->findOrFail($id);

        return response()->json([
            'status'  => true,
            'message' => 'Shift summary retrieved.',
            'data'    => $shift,
        ]);
    }
}
