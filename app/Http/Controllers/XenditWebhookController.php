<?php

namespace App\Http\Controllers;

use App\Events\PaymentSettledEvent;
use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    /**
     * Menangani callback notifikasi pembayaran otomatis dari Xendit Payment Gateway.
     */
    public function handle(Request $request): JsonResponse
    {
        $incomingToken = $request->header('x-callback-token');
        $expectedToken = config('services.xendit.webhook_token');

        // Validasi Keamanan Callback Token
        if (empty($expectedToken) || ! is_string($incomingToken) || ! hash_equals($expectedToken, $incomingToken)) {
            Log::warning('Xendit Webhook Unauthorized Token Attempt', [
                'ip' => $request->ip(),
                'incoming_token' => $incomingToken,
            ]);

            return response()->json(['message' => 'Unauthorized token'], 401);
        }

        $payload = $request->all();
        $status = strtoupper((string) ($payload['status'] ?? ''));
        $externalId = (string) ($payload['external_id'] ?? '');
        $xenditId = (string) ($payload['id'] ?? '');
        $paymentMethod = (string) ($payload['payment_method'] ?? $payload['payment_channel'] ?? 'xendit_invoice');

        Log::info('Xendit Webhook Received', [
            'status' => $status,
            'external_id' => $externalId,
            'xendit_id' => $xenditId,
        ]);

        if (! in_array($status, ['PAID', 'SETTLED'])) {
            return response()->json([
                'status' => true,
                'message' => 'Notification acknowledged (non-paid status: '.$status.')',
            ]);
        }

        // 1. Cek apakah ini pembayaran untuk SIMRS Billing (external_id: BILL-... atau QRIS-...)
        if (str_starts_with($externalId, 'BILL-') || str_starts_with($externalId, 'QRIS-') || ! empty($xenditId)) {
            $billing = null;

            if (str_starts_with($externalId, 'BILL-') || str_starts_with($externalId, 'QRIS-')) {
                $parts = explode('-', $externalId);
                $billingId = isset($parts[1]) ? (int) $parts[1] : 0;
                $billing = Billing::where('billing_id', $billingId)->first();
            }

            if (! $billing && ! empty($xenditId)) {
                $billing = Billing::where('xendit_id', $xenditId)->first();
            }

            if ($billing) {
                DB::transaction(function () use ($billing, $paymentMethod) {
                    $billing->update([
                        'status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_at' => now(),
                    ]);

                    // Update status appointment / antrean
                    if ($billing->reservation) {
                        $billing->reservation->update(['status' => 'completed']);

                        $prescription = $billing->reservation->medicalRecord?->prescription;
                        if ($prescription && in_array($prescription->status, ['menunggu', 'diproses'])) {
                            $prescription->update(['status' => 'selesai']);
                        }
                    }
                });

                // Siarkan konfirmasi pelunasan instan via Reverb WebSockets
                event(new PaymentSettledEvent(
                    billingId: (int) $billing->billing_id,
                    invoiceNumber: (string) $billing->invoice_number,
                    status: 'paid',
                    paymentMethod: (string) $paymentMethod,
                    paidAmount: (float) $billing->total_amount,
                    paidAt: now()->toIso8601String()
                ));

                Log::info("Billing #{$billing->invoice_number} successfully marked as PAID via Xendit webhook.");

                return response()->json([
                    'status' => true,
                    'message' => 'Billing successfully marked as PAID',
                ]);
            }
        }

        // 2. Fallback untuk Payment legacy (external_id: PAY-...)
        if (str_starts_with($externalId, 'PAY-')) {
            $parts = explode('-', $externalId);
            $paymentId = isset($parts[1]) ? (int) $parts[1] : 0;
            $payment = Payment::find($paymentId);

            if ($payment) {
                DB::transaction(function () use ($payment) {
                    $payment->update([
                        'status' => 'Paid',
                        'paid_amount' => $payment->payment_total,
                    ]);

                    if ($payment->registration) {
                        $payment->registration->update(['status' => 'Selesai']);
                    }
                });

                return response()->json([
                    'status' => true,
                    'message' => 'Legacy payment successfully updated',
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Webhook received, no matching record found',
        ]);
    }

    /**
     * Menangani callback webhook spesifik untuk Xendit Dynamic QR Code (QRIS).
     */
    public function handleQrCallback(Request $request): JsonResponse
    {
        $incomingToken = $request->header('x-callback-token');
        $expectedToken = config('services.xendit.webhook_token');

        // Validasi Keamanan Callback Token
        if (empty($expectedToken) || ! is_string($incomingToken) || ! hash_equals($expectedToken, $incomingToken)) {
            Log::warning('Xendit QR Webhook Unauthorized Token Attempt', [
                'ip' => $request->ip(),
                'incoming_token' => $incomingToken,
            ]);

            return response()->json(['message' => 'Unauthorized token'], 401);
        }

        $payload = $request->all();
        // Xendit QR callback bisa memiliki data di root atau di dalam nested 'data' key
        $data = $payload['data'] ?? $payload;
        $status = strtoupper((string) ($data['status'] ?? $payload['status'] ?? ''));
        $externalId = (string) ($data['external_id'] ?? $payload['external_id'] ?? '');
        $qrId = (string) ($data['id'] ?? $data['qr_id'] ?? $payload['id'] ?? '');
        $paymentMethod = 'QRIS';

        Log::info('Xendit QR Webhook Received', [
            'status' => $status,
            'external_id' => $externalId,
            'qr_id' => $qrId,
        ]);

        if (! in_array($status, ['COMPLETED', 'PAID', 'SETTLED', 'ACTIVE'])) {
            return response()->json([
                'status' => true,
                'message' => 'QR webhook acknowledged (status: '.$status.')',
            ]);
        }

        if (in_array($status, ['COMPLETED', 'PAID', 'SETTLED'])) {
            $billing = null;

            if (str_starts_with($externalId, 'QRIS-') || str_starts_with($externalId, 'BILL-')) {
                $parts = explode('-', $externalId);
                $billingId = isset($parts[1]) ? (int) $parts[1] : 0;
                $billing = Billing::where('billing_id', $billingId)->first();
            }

            if (! $billing && ! empty($qrId)) {
                $billing = Billing::where('xendit_id', $qrId)->first();
            }

            if ($billing) {
                DB::transaction(function () use ($billing, $paymentMethod) {
                    $billing->update([
                        'status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_at' => now(),
                    ]);

                    if ($billing->reservation) {
                        $billing->reservation->update(['status' => 'completed']);

                        $prescription = $billing->reservation->medicalRecord?->prescription;
                        if ($prescription && in_array($prescription->status, ['menunggu', 'diproses'])) {
                            $prescription->update(['status' => 'selesai']);
                        }
                    }
                });

                // Siarkan konfirmasi pelunasan instan via Reverb WebSockets
                event(new PaymentSettledEvent(
                    billingId: (int) $billing->billing_id,
                    invoiceNumber: (string) $billing->invoice_number,
                    status: 'paid',
                    paymentMethod: (string) $paymentMethod,
                    paidAmount: (float) $billing->total_amount,
                    paidAt: now()->toIso8601String()
                ));

                Log::info("Billing #{$billing->invoice_number} successfully marked as PAID via QRIS webhook.");

                return response()->json([
                    'status' => true,
                    'message' => 'QRIS payment successfully settled',
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'QR webhook acknowledged, no matching billing found',
        ]);
    }
}
