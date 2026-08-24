<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected ?string $secretKey;

    protected string $invoiceUrl;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
        $this->invoiceUrl = config('services.xendit.invoice_url', 'https://api.xendit.co/v2/invoices');
    }

    /**
     * Membuat Invoice Pembayaran Online di Xendit untuk Model Payment legacy.
     */
    public function createInvoice(Payment $payment): array
    {
        $registration = $payment->registration;
        $patient = $registration->patient;

        // Format Order ID Unik: PAY-{payment_id}-{timestamp}
        $externalId = 'PAY-'.$payment->payment_id.'-'.time();

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) $payment->payment_total,
            'description' => 'Pembayaran Tagihan Medis - '.($registration->poli->name_poli ?? 'Poliklinik'),
            'invoice_duration' => 86400, // 24 jam
            'customer' => [
                'given_names' => $patient->name ?? 'Pasien',
                'email' => $patient->user->email ?? 'patient@hospital.com',
                'mobile_number' => $patient->number_phone ?? '081234567890',
            ],
            'items' => [
                [
                    'name' => 'Biaya Konsultasi & Obat ('.($registration->poli->name_poli ?? 'Poliklinik').')',
                    'quantity' => 1,
                    'price' => (int) $payment->payment_total,
                ],
            ],
            'success_redirect_url' => url('/patient/dashboard'),
            'failure_redirect_url' => url('/patient/dashboard'),
        ];

        return $this->sendInvoiceRequest($payload, $externalId);
    }

    /**
     * Membuat Invoice Pembayaran Online di Xendit untuk Model Billing Kasir.
     */
    public function createBillingInvoice(Billing $billing): array
    {
        $billing->loadMissing(['patient.user', 'items', 'reservation.doctorSchedule.poli']);
        $patient = $billing->patient;
        $poliName = $billing->reservation?->doctorSchedule?->poli?->name_poli ?? 'Poliklinik Rawat Jalan';

        // Format Order ID Unik: BILL-{billing_id}-{timestamp}
        $externalId = 'BILL-'.$billing->billing_id.'-'.time();

        // Siapkan item rincian untuk breakdown invoice Xendit
        $items = [];
        if ($billing->items && $billing->items->isNotEmpty()) {
            foreach ($billing->items as $item) {
                $items[] = [
                    'name' => (string) $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'price' => (int) round((float) $item->unit_price),
                ];
            }
        } else {
            $items[] = [
                'name' => 'Tagihan Rawat Jalan ('.$poliName.')',
                'quantity' => 1,
                'price' => (int) round((float) $billing->total_amount),
            ];
        }

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) round((float) $billing->total_amount),
            'description' => 'Pembayaran Tagihan SIMRS #'.$billing->invoice_number.' ('.$poliName.')',
            'invoice_duration' => 86400, // 24 jam
            'customer' => [
                'given_names' => $patient?->name ?? 'Pasien SIMRS',
                'email' => $patient?->user?->email ?? 'pasien@hospital.com',
                'mobile_number' => $patient?->number_phone ?? '081234567890',
            ],
            'items' => $items,
            'payment_methods' => ['QRIS', 'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'OVO', 'DANA', 'SHOPEEPAY'],
            'success_redirect_url' => url('/staff/billing/'.$billing->billing_id),
            'failure_redirect_url' => url('/staff/billing/'.$billing->billing_id),
        ];

        return $this->sendInvoiceRequest($payload, $externalId);
    }

    /**
     * Membuat QRIS Dinamis Instan (Dynamic QR Code API) untuk POS Kasir SIMRS.
     * Mengembalikan qr_string standar EMVCo / ASPI untuk di-render langsung di layar kasir.
     */
    public function createDynamicQris(Billing $billing): array
    {
        $externalId = 'QRIS-'.$billing->billing_id.'-'.time();
        $amount = (int) round((float) $billing->total_amount);

        // Mock fallback untuk environment testing / sandbox offline
        if (empty($this->secretKey) || str_starts_with((string) $this->secretKey, 'mock_') || app()->environment('testing')) {
            $mockChecksum = strtoupper(substr(md5($externalId), 0, 4));
            $mockQrString = '00020101021226680016ID.CO.XENDIT.WWW0118936009180000000000520458125303360540'.strlen((string) $amount).$amount.'5802ID5919HOSPITAL POPULATION6007JAKARTA62070703A016304'.$mockChecksum;

            return [
                'id' => 'qr_'.md5($externalId),
                'external_id' => $externalId,
                'qr_string' => $mockQrString,
                'status' => 'ACTIVE',
                'amount' => $amount,
                'type' => 'DYNAMIC',
            ];
        }

        try {
            $callbackUrl = url('/api/webhooks/xendit/qr');
            // Jika berjalan di localhost atau non-HTTPS domain, gunakan valid dummy HTTPS callback untuk sandbox Xendit
            if (str_contains($callbackUrl, 'localhost') || str_contains($callbackUrl, '127.0.0.1')) {
                $callbackUrl = 'https://hospital-simrs.test/api/webhooks/xendit/qr';
            }

            $payload = [
                'external_id' => $externalId,
                'type' => 'DYNAMIC',
                'currency' => 'IDR',
                'amount' => $amount,
                'callback_url' => $callbackUrl,
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->acceptJson()
                ->timeout(15)
                ->post('https://api.xendit.co/qr_codes', $payload);

            if (! $response->successful()) {
                Log::warning('Xendit Dynamic QR Creation returned non-200: '.$response->body(), ['payload' => $payload]);

                // Di environment development/local, jika gagal validasi sandbox, fallback ke valid EMVCo format
                if (app()->environment('local', 'testing')) {
                    $mockChecksum = strtoupper(substr(md5($externalId), 0, 4));
                    $mockQrString = '00020101021226680016ID.CO.XENDIT.WWW0118936009180000000000520458125303360540'.strlen((string) $amount).$amount.'5802ID5919HOSPITAL POPULATION6007JAKARTA62070703A016304'.$mockChecksum;

                    return [
                        'id' => 'qr_'.md5($externalId),
                        'external_id' => $externalId,
                        'qr_string' => $mockQrString,
                        'status' => 'ACTIVE',
                        'amount' => $amount,
                        'type' => 'DYNAMIC',
                    ];
                }

                throw new Exception('Gagal membuat QRIS Xendit: '.$response->body());
            }

            $data = $response->json();

            return [
                'id' => $data['id'] ?? ('qr_'.md5($externalId)),
                'external_id' => $data['external_id'] ?? $externalId,
                'qr_string' => $data['qr_string'] ?? '',
                'status' => $data['status'] ?? 'ACTIVE',
                'amount' => $data['amount'] ?? $amount,
                'type' => 'DYNAMIC',
            ];
        } catch (Exception $e) {
            Log::error('Xendit QR Service Exception: '.$e->getMessage());

            if (app()->environment('local', 'testing')) {
                $mockChecksum = strtoupper(substr(md5($externalId), 0, 4));
                $mockQrString = '00020101021226680016ID.CO.XENDIT.WWW0118936009180000000000520458125303360540'.strlen((string) $amount).$amount.'5802ID5919HOSPITAL POPULATION6007JAKARTA62070703A016304'.$mockChecksum;

                return [
                    'id' => 'qr_'.md5($externalId),
                    'external_id' => $externalId,
                    'qr_string' => $mockQrString,
                    'status' => 'ACTIVE',
                    'amount' => $amount,
                    'type' => 'DYNAMIC',
                ];
            }

            throw $e;
        }
    }

    /**
     * Eksekusi HTTP Request ke Xendit API atau fallback ke Mock URL di environment non-live.
     */
    protected function sendInvoiceRequest(array $payload, string $externalId): array
    {
        if (empty($this->secretKey) || str_starts_with((string) $this->secretKey, 'mock_') || app()->environment('testing')) {
            // Mock response untuk environment testing / sandbox offline
            return [
                'id' => 'xendit_inv_'.md5($externalId),
                'external_id' => $externalId,
                'status' => 'PENDING',
                'invoice_url' => 'https://checkout-staging.xendit.co/web/'.md5($externalId),
                'amount' => $payload['amount'],
            ];
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->acceptJson()
                ->timeout(15)
                ->post($this->invoiceUrl, $payload);

            if (! $response->successful()) {
                Log::error('Xendit Invoice Creation Failed: '.$response->body(), ['payload' => $payload]);
                throw new Exception('Gagal membuat tagihan Xendit: '.$response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Xendit Service Exception: '.$e->getMessage());
            throw $e;
        }
    }
}
