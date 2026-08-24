<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected XenditService $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Menampilkan detail tagihan pembayaran
     */
    public function show(int $id): JsonResponse
    {
        $payment = Payment::with([
            'registration.patient',
            'registration.doctor.specialization',
            'registration.poli',
            'registration.inspection',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $payment,
        ]);
    }

    /**
     * JALUR NON-TUNAI: Generate Invoice URL Xendit (QRIS, VA, E-Wallet)
     */
    public function payOnline(int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status === 'Lunas') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tagihan ini sudah lunas.',
            ], 422);
        }

        try {
            $invoiceData = $this->xenditService->createInvoice($payment);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice berhasil dibuat. Silakan selesaikan pembayaran.',
                'invoice_url' => $invoiceData['invoice_url'],
                'expiry_date' => $invoiceData['expiry_date'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * JALUR TUNAI: Pelunasan Manual di Kasir oleh Perawat / Petugas
     */
    public function payCash(Request $request, int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status === 'Lunas') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tagihan ini sudah berstatus lunas.',
            ], 422);
        }

        $payment->update([
            'payment_method' => 'Tunai',
            'payment_status' => 'Lunas',
            'payment_date' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran tunai berhasil diverifikasi. Status tagihan lunas.',
            'data' => $payment,
        ]);
    }

    /**
     * WEBHOOK OTOMATIS: Diterima dari server Xendit saat pasien melunasi pembayaran online
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Xendit Webhook Received:', $request->all());

        $externalId = (string) $request->input('external_id', '');
        $status = strtoupper($request->input('status', ''));
        $paymentMethodType = $request->input('payment_method');
        $paidAmountFromXendit = (float) $request->input('paid_amount', $request->input('amount', 0));

        // 1. Validasi format invoice resmi aplikasi: "PAY-{id}-{timestamp}"
        if (! preg_match('/^PAY-(\d+)/i', $externalId, $matches)) {
            // Jika format dummy/test dari Xendit (misal: "invoice_123124123"), respon 200 OK
            return response()->json([
                'status' => 'success',
                'message' => 'Test callback diterima tanpa query database.',
            ], 200);
        }

        $paymentId = (int) $matches[1];
        $payment = Payment::find($paymentId);

        // 2. Jika ID transaksi resmi tidak ditemukan di DB
        if (! $payment) {
            Log::warning("Payment ID {$paymentId} tidak ditemukan di database.");

            return response()->json([
                'status' => 'ignored',
                'message' => 'Payment tidak ditemukan di database.',
            ], 200); // Berikan 200 agar Xendit tidak terus-menerus mengulang request
        }

        // 3. Proses pelunasan / DP
        if (in_array($status, ['PAID', 'SETTLED'])) {
            $totalPaid = ($payment->paid_amount ?? 0) + $paidAmountFromXendit;
            $isFullyPaid = $totalPaid >= $payment->payment_total;

            // Gunakan 'Paid' untuk pelunasan penuh
            $newStatus = $isFullyPaid ? 'Paid' : 'DP';

            $mappedMethod = match (strtoupper($paymentMethodType ?? '')) {
                'QR_CODE', 'QRIS' => 'QRIS',
                'CREDIT_CARD' => 'Kredit Card',
                'BANK_TRANSFER' => 'Debit Card',
                default => 'QRIS',
            };

            $payment->update([
                'paid_amount' => $totalPaid,
                'payment_status' => $newStatus,
                'payment_method' => $mappedMethod,
                'payment_date' => now(),
            ]);

            if ($isFullyPaid && $payment->registration) {
                $payment->registration->update(['status' => 'Selesai']);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook berhasil diproses',
        ], 200);
    }

    public function createOnlinePayment(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $isDp = $request->boolean('is_dp', false);
        $amount = $isDp ? ($payment->payment_total * 0.5) : ($payment->payment_total - ($payment->paid_amount ?? 0));
        $description = ($isDp ? 'Pembayaran DP Tagihan #' : 'Pelunasan Tagihan #').$payment->payment_id;

        $secretKey = config('services.xendit.secret_key');
        $invoiceUrl = config('services.xendit.invoice_url');

        $response = Http::withBasicAuth($secretKey, '')
            ->post($invoiceUrl, [
                'external_id' => 'PAY-'.$payment->payment_id.'-'.time(),
                'amount' => (int) $amount,
                'description' => $description,
                'invoice_duration' => 86400,
                'currency' => 'IDR',
            ]);

        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat invoice Xendit.',
                'error' => $response->json(),
            ], $response->status());
        }

        $invoiceData = $response->json();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice berhasil dibuat.',
            'type' => $isDp ? 'DP' : 'Pelunasan',
            'amount' => (int) $amount,
            'invoice_url' => $invoiceData['invoice_url'],
            'expiry_date' => $invoiceData['expiry_date'] ?? null,
        ]);
    }
}
