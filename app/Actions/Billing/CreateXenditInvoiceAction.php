<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Services\XenditService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateXenditInvoiceAction
{
    public function __construct(
        protected XenditService $xenditService
    ) {}

    /**
     * Membuat invoice Xendit dan menyimpan record Billing baru untuk Appointment.
     *
     * @param  Appointment  $appointment  Data janji temu rawat jalan
     * @param  float  $amount  Nominal tagihan
     * @param  string|null  $description  Keterangan tagihan
     * @param  int|null  $nurseId  ID perawat yang memproses transaksi
     * @param  string  $paymentType  Tipe pembayaran ('invoice' atau 'qris')
     * @return Billing Model tagihan yang telah tersimpan
     *
     * @throws Exception
     */
    public function execute(
        Appointment $appointment,
        float $amount,
        ?string $description = null,
        ?int $nurseId = null,
        string $paymentType = 'invoice'
    ): Billing {
        $appointment->loadMissing(['patient.user', 'doctorSchedule.poli', 'doctorSchedule.doctor', 'medicalRecord.prescription.items.medicine']);

        $timestamp = time();
        $externalId = 'INV-'.$appointment->appointment_id.'-'.$timestamp;
        $roundedAmount = (int) round($amount);

        $patient = $appointment->patient;
        $patientEmail = $patient?->user->email ?? 'pasien@hospital.com';
        $patientName = $patient?->name ?? 'Pasien SIMRS';
        $poliName = $appointment->doctorSchedule?->poli->name_poli ?? 'Poliklinik Rawat Jalan';
        $invoiceDescription = $description ?: "Tagihan Layanan Rawat Jalan - {$poliName} ({$patientName})";

        // 1. Eksekusi Request ke Xendit Invoices API
        $invoiceData = $this->createXenditInvoicePayload(
            externalId: $externalId,
            amount: $roundedAmount,
            payerEmail: $patientEmail,
            payerName: $patientName,
            description: $invoiceDescription
        );

        // 2. Jika tipe pembayaran adalah QRIS, generate string QRIS dinamis juga
        $qrString = null;
        $qrId = null;
        if ($paymentType === 'qris') {
            try {
                // Simulasikan atau panggil Dynamic QRIS
                $tempBilling = new Billing([
                    'billing_id' => $appointment->appointment_id,
                    'total_amount' => $roundedAmount,
                ]);
                $qrisData = $this->xenditService->createDynamicQris($tempBilling);
                $qrString = $qrisData['qr_string'] ?? null;
                $qrId = $qrisData['id'] ?? null;
            } catch (Exception $e) {
                Log::warning('Fallback to Invoice QRIS: '.$e->getMessage());
            }
        }

        // 3. Simpan data Billing ke Database secara Transaksional
        return DB::transaction(function () use (
            $appointment,
            $nurseId,
            $externalId,
            $roundedAmount,
            $invoiceData,
            $paymentType,
            $qrString,
            $qrId
        ) {
            $billing = Billing::create([
                'reservation_id' => $appointment->appointment_id,
                'appointment_id' => $appointment->appointment_id,
                'patient_id' => $appointment->patient_id,
                'processed_by_nurse_id' => $nurseId,
                'invoice_number' => $externalId,
                'external_id' => $externalId,
                'total_amount' => $roundedAmount,
                'amount' => $roundedAmount,
                'status' => 'pending',
                'payment_method' => $paymentType === 'qris' ? 'xendit_qris' : 'xendit_invoice',
                'xendit_id' => $qrId ?: ($invoiceData['id'] ?? null),
                'xendit_invoice_id' => $invoiceData['id'] ?? null,
                'xendit_payment_url' => $qrString ?: ($invoiceData['invoice_url'] ?? null),
                'invoice_url' => $invoiceData['invoice_url'] ?? null,
                'payment_details' => json_encode([
                    'qr_string' => $qrString,
                    'xendit_invoice_id' => $invoiceData['id'] ?? null,
                    'invoice_url' => $invoiceData['invoice_url'] ?? null,
                    'expiry_date' => $invoiceData['expiry_date'] ?? null,
                ]),
            ]);

            // Catat Billing Item rincian tagihan
            BillingItem::create([
                'billing_id' => $billing->billing_id,
                'item_type' => 'medical_service',
                'item_name' => 'Layanan Medis & Tindakan Rawat Jalan',
                'quantity' => 1,
                'unit_price' => $roundedAmount,
                'subtotal' => $roundedAmount,
            ]);

            Log::info("Xendit Invoice created successfully for Appointment #{$appointment->appointment_id}", [
                'external_id' => $externalId,
                'invoice_id' => $invoiceData['id'] ?? null,
                'amount' => $roundedAmount,
            ]);

            return $billing;
        });
    }

    /**
     * Memanggil endpoint POST https://api.xendit.co/v2/invoices
     * dengan Basic Auth dan fallback mock pada testing environment.
     *
     * @return array<string, mixed>
     */
    protected function createXenditInvoicePayload(
        string $externalId,
        int $amount,
        string $payerEmail,
        string $payerName,
        string $description
    ): array {
        $secretKey = config('services.xendit.secret_key');
        $invoiceUrl = config('services.xendit.invoice_url', 'https://api.xendit.co/v2/invoices');

        // Mock response pada environment testing / tanpa key live
        if (empty($secretKey) || str_starts_with((string) $secretKey, 'mock_') || app()->environment('testing')) {
            $mockInvoiceId = 'inv_'.md5($externalId);

            return [
                'id' => $mockInvoiceId,
                'external_id' => $externalId,
                'status' => 'PENDING',
                'amount' => $amount,
                'payer_email' => $payerEmail,
                'description' => $description,
                'invoice_url' => 'https://checkout-staging.xendit.co/web/'.$mockInvoiceId,
                'expiry_date' => now()->addDay()->toIso8601String(),
            ];
        }

        $payload = [
            'external_id' => $externalId,
            'amount' => $amount,
            'payer_email' => $payerEmail,
            'description' => $description,
            'invoice_duration' => 86400, // Durasi 24 jam
            'payment_methods' => ['QRIS', 'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'OVO', 'DANA', 'SHOPEEPAY'],
            'customer' => [
                'given_names' => $payerName,
                'email' => $payerEmail,
            ],
            'success_redirect_url' => url('/staff'),
            'failure_redirect_url' => url('/staff'),
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->acceptJson()
                ->timeout(15)
                ->post($invoiceUrl, $payload);

            if (! $response->successful()) {
                Log::error('Xendit Invoice Creation API Failed: '.$response->body(), [
                    'payload' => $payload,
                    'status' => $response->status(),
                ]);

                // Di environment development local, fallback ke mock invoice jika sandbox error
                if (app()->environment('local')) {
                    $mockInvoiceId = 'inv_'.md5($externalId);

                    return [
                        'id' => $mockInvoiceId,
                        'external_id' => $externalId,
                        'status' => 'PENDING',
                        'amount' => $amount,
                        'payer_email' => $payerEmail,
                        'description' => $description,
                        'invoice_url' => 'https://checkout-staging.xendit.co/web/'.$mockInvoiceId,
                        'expiry_date' => now()->addDay()->toIso8601String(),
                    ];
                }

                throw new Exception('Gagal membuat tagihan Xendit: '.$response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Xendit Service Connection Error: '.$e->getMessage(), [
                'external_id' => $externalId,
            ]);

            if (app()->environment('local')) {
                $mockInvoiceId = 'inv_'.md5($externalId);

                return [
                    'id' => $mockInvoiceId,
                    'external_id' => $externalId,
                    'status' => 'PENDING',
                    'amount' => $amount,
                    'payer_email' => $payerEmail,
                    'description' => $description,
                    'invoice_url' => 'https://checkout-staging.xendit.co/web/'.$mockInvoiceId,
                    'expiry_date' => now()->addDay()->toIso8601String(),
                ];
            }

            throw $e;
        }
    }
}
