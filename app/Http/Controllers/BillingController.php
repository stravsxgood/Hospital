<?php

namespace App\Http\Controllers;

use App\Actions\Billing\CreateXenditInvoiceAction;
use App\Events\PaymentSettledEvent;
use App\Http\Requests\StoreBillingRequest;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Services\XenditService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class BillingController
 *
 * Modul Manajemen Tagihan & Kasir Rumah Sakit.
 * Dibatasi secara ketat hanya untuk Staf/Perawat Tetap (Pekerja) via Gate 'access-pekerja-only'.
 */
class BillingController extends Controller
{
    public function __construct(
        protected XenditService $xenditService
    ) {}

    /**
     * Menghitung rincian biaya otomatis (Jasa Dokter, Administrasi, Obat) untuk Appointment.
     */
    public function calculateAmount(int $appointmentId): JsonResponse
    {
        $appointment = Appointment::with([
            'patient',
            'doctorSchedule.doctor',
            'doctorSchedule.poli',
            'medicalRecord.prescription.items.medicine',
            'billing',
        ])->findOrFail($appointmentId);

        $consultationFee = 150000.00;
        $adminFee = 25000.00;
        $medicineTotal = 0.00;
        $items = [];

        $items[] = [
            'type' => 'consultation',
            'name' => 'Konsultasi Dokter ('.($appointment->doctorSchedule?->doctor->name ?? 'Dokter Spesialis').')',
            'price' => $consultationFee,
            'qty' => 1,
            'subtotal' => $consultationFee,
        ];

        $items[] = [
            'type' => 'admin',
            'name' => 'Administrasi & Sarana Rawat Jalan',
            'price' => $adminFee,
            'qty' => 1,
            'subtotal' => $adminFee,
        ];

        $prescription = $appointment->medicalRecord?->prescription;
        if ($prescription) {
            foreach ($prescription->items as $item) {
                $med = $item->medicine;
                $price = $med ? (float) $med->price : 0.00;
                $qty = (int) $item->quantity;
                $subtotal = $price * $qty;
                $medicineTotal += $subtotal;

                $items[] = [
                    'type' => 'medicine',
                    'name' => ($med?->name_medicine ?? 'Obat Farmasi').' ('.$item->dosage.')',
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }
        }

        $totalAmount = $consultationFee + $adminFee + $medicineTotal;

        return response()->json([
            'status' => true,
            'appointment_id' => $appointment->appointment_id,
            'patient_name' => $appointment->patient?->name,
            'poli_name' => $appointment->doctorSchedule?->poli?->name_poli,
            'doctor_name' => $appointment->doctorSchedule?->doctor?->name,
            'consultation_fee' => $consultationFee,
            'admin_fee' => $adminFee,
            'medicine_total' => $medicineTotal,
            'total_amount' => $totalAmount,
            'items' => $items,
            'existing_billing' => $appointment->billing,
        ]);
    }

    /**
     * Membuat tagihan Xendit baru (Invoices / QRIS) langsung dari Workspace Staf/Perawat.
     */
    public function store(StoreBillingRequest $request, CreateXenditInvoiceAction $action): RedirectResponse|JsonResponse
    {
        /** @var Appointment $appointment */
        $appointment = Appointment::with([
            'patient.user',
            'doctorSchedule.doctor',
            'doctorSchedule.poli',
            'medicalRecord.prescription.items.medicine',
        ])->findOrFail((int) $request->validated('appointment_id'));

        $amount = (float) ($request->validated('amount') ?? 0);

        // Jika perawat tidak mengisi nominal manual, hitung otomatis secara presisi
        if ($amount <= 0) {
            $consultationFee = 150000.00;
            $adminFee = 25000.00;
            $medicineTotal = 0.00;

            $prescription = $appointment->medicalRecord?->prescription;
            if ($prescription) {
                foreach ($prescription->items as $item) {
                    $med = $item->medicine;
                    $unitPrice = $med ? (float) $med->price : 0.00;
                    $qty = (int) $item->quantity;
                    $medicineTotal += ($unitPrice * $qty);
                }
            }

            $amount = $consultationFee + $adminFee + $medicineTotal;
        }

        $description = $request->validated('description');
        $paymentType = (string) ($request->validated('payment_type') ?? 'invoice');
        $nurseId = $request->user()?->nurse?->nurse_id;

        $billing = $action->execute(
            appointment: $appointment,
            amount: $amount,
            description: $description,
            nurseId: $nurseId,
            paymentType: $paymentType
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tagihan Xendit berhasil dibuat.',
                'invoice_url' => $billing->invoice_url,
                'xendit_payment_url' => $billing->xendit_payment_url,
                'billing' => $billing,
            ], 201);
        }

        return redirect()->back()
            ->with('success', 'Tagihan #'.$billing->invoice_number.' berhasil diterbitkan.')
            ->with('invoice_url', $billing->invoice_url)
            ->with('billing', $billing);
    }

    /**
     * Menampilkan daftar tagihan medis (Billing & Kasir).
     */
    public function index(Request $request): Response
    {
        // Enforce RBAC Gate
        Gate::authorize('access-pekerja-only');

        $search = $request->query('search');
        $status = $request->query('status');
        $date = $request->query('date');

        // 1. Query Data Tagihan dengan Eager Loading Penuh (Zero N+1)
        $billingsQuery = Billing::with([
            'patient.user',
            'reservation.doctorSchedule.doctor.specialization',
            'reservation.doctorSchedule.poli',
            'items',
            'processedByNurse',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('resident_n', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($date, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->latest('created_at');

        $billings = $billingsQuery->paginate(15)->withQueryString();

        // 2. Agregasi Statistik Keuangan Kasir via PostgreSQL Aggregates
        $stats = [
            'total_invoices' => Billing::count(),
            'unpaid_count' => Billing::where('status', 'unpaid')->count(),
            'pending_count' => Billing::where('status', 'pending')->count(),
            'paid_count' => Billing::where('status', 'paid')->count(),
            'total_revenue' => (float) Billing::where('status', 'paid')->sum('total_amount'),
            'today_revenue' => (float) Billing::where('status', 'paid')->whereDate('paid_at', today())->sum('total_amount'),
        ];

        // 3. Ambil Pasien yang selesai konsultasi namun belum dibuatkan billing
        $unbilledConsultations = Appointment::with([
            'patient',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'medicalRecord.prescription.items.medicine',
        ])
            ->whereIn('status', ['in_progress', 'completed'])
            ->whereDoesntHave('billing')
            ->latest('updated_at')
            ->take(10)
            ->get();

        return Inertia::render('staff/Billing/Index', [
            'billings' => $billings,
            'stats' => $stats,
            'unbilledConsultations' => $unbilledConsultations,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date' => $date,
            ],
        ]);
    }

    /**
     * Menampilkan detail tagihan dan rincian item pembayaran.
     */
    public function show(int $id): Response|JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $billing = Billing::with([
            'patient.user',
            'reservation.doctorSchedule.doctor.specialization',
            'reservation.doctorSchedule.poli',
            'reservation.doctorSchedule.room',
            'reservation.medicalRecord.prescription.items.medicine',
            'items',
            'processedByNurse',
        ])->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'data' => $billing,
            ]);
        }

        return Inertia::render('staff/Billing/Show', [
            'billing' => $billing,
        ]);
    }

    /**
     * Membuat data tagihan baru secara otomatis dari sesi konsultasi / rekam medis pasien.
     */
    public function createFromReservation(int $reservationId): RedirectResponse|JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $appointment = Appointment::with([
            'patient',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'medicalRecord.prescription.items.medicine',
            'billing',
        ])->findOrFail($reservationId);

        // Jika sudah ada billing sebelumnya, langsung arahkan ke billing tersebut
        if ($appointment->billing) {
            return redirect()->route('staff.billing.show', $appointment->billing->billing_id)
                ->with('success', 'Tagihan untuk pasien ini sudah tersedia.');
        }

        $billing = DB::transaction(function () use ($appointment) {
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

            // Biaya jasa dokter / konsultasi rawat jalan
            $consultationFee = 150000.00;
            $adminFee = 25000.00;
            $itemsPayload = [];

            $itemsPayload[] = [
                'item_type' => 'consultation_fee',
                'item_name' => 'Jasa Konsultasi Dokter ('.($appointment->doctorSchedule?->doctor->name ?? 'Dokter Spesialis').')',
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'subtotal' => $consultationFee,
            ];

            $itemsPayload[] = [
                'item_type' => 'procedure',
                'item_name' => 'Biaya Administrasi & Sarana Rawat Jalan',
                'quantity' => 1,
                'unit_price' => $adminFee,
                'subtotal' => $adminFee,
            ];

            $totalAmount = $consultationFee + $adminFee;

            // Hitung biaya resep obat elektronik jika dokter memberikan resep
            $prescription = $appointment->medicalRecord?->prescription;
            if ($prescription) {
                foreach ($prescription->items as $item) {
                    $med = $item->medicine;
                    $unitPrice = $med ? (float) $med->price : 0.00;
                    $qty = (int) $item->quantity;
                    $subtotal = $unitPrice * $qty;

                    $itemsPayload[] = [
                        'item_type' => 'medicine',
                        'item_name' => 'Obat: '.($med?->name_medicine ?? 'Farmasi').' ('.$item->dosage.')',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];

                    $totalAmount += $subtotal;
                }
            }

            // Buat Record Billing
            $newBilling = Billing::create([
                'reservation_id' => $appointment->appointment_id,
                'patient_id' => $appointment->patient_id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'status' => 'unpaid',
            ]);

            // Buat Billing Items
            foreach ($itemsPayload as $item) {
                BillingItem::create([
                    'billing_id' => $newBilling->billing_id,
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $newBilling;
        });

        if (request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tagihan berhasil dibuat.',
                'data' => $billing,
            ], 201);
        }

        return redirect()->route('staff.billing.show', $billing->billing_id)
            ->with('success', 'Tagihan #'.$billing->invoice_number.' berhasil dihitung dan dibuat.');
    }

    /**
     * Memproses pelunasan tagihan secara TUNAI (Cash Payment Settlement).
     */
    public function payCash(Request $request, int $id): JsonResponse|RedirectResponse
    {
        Gate::authorize('access-pekerja-only');

        $request->validate([
            'cash_received' => ['required', 'numeric', 'min:0'],
        ]);

        $nurse = $request->user()?->nurse;
        $nurseId = $nurse?->nurse_id;
        $cashReceived = (float) $request->input('cash_received');

        $billing = DB::transaction(function () use ($id, $nurseId, $cashReceived) {
            $bill = Billing::where('billing_id', $id)->lockForUpdate()->firstOrFail();

            if ($bill->status === 'paid') {
                throw new Exception('Tagihan ini sudah lunas sebelumnya.');
            }

            if ($cashReceived < (float) $bill->total_amount) {
                throw new Exception('Uang tunai yang diterima (Rp '.number_format($cashReceived, 0, ',', '.').') kurang dari total tagihan (Rp '.number_format((float) $bill->total_amount, 0, ',', '.').').');
            }

            $bill->update([
                'status' => 'paid',
                'payment_method' => 'cash',
                'processed_by_nurse_id' => $nurseId,
                'paid_at' => now(),
            ]);

            // Sinkronisasi status reservasi & resep obat
            if ($bill->reservation) {
                $bill->reservation->update(['status' => 'completed']);

                $prescription = $bill->reservation->medicalRecord?->prescription;
                if ($prescription && in_array($prescription->status, ['menunggu', 'diproses'])) {
                    $prescription->update(['status' => 'selesai']);
                }
            }

            return $bill;
        });

        $change = $cashReceived - (float) $billing->total_amount;

        // Siarkan konfirmasi pembayaran instan ke channel WebSocket private billing
        event(new PaymentSettledEvent(
            billingId: (int) $billing->billing_id,
            invoiceNumber: (string) $billing->invoice_number,
            status: 'paid',
            paymentMethod: (string) $billing->payment_method,
            paidAmount: (float) $billing->total_amount,
            paidAt: $billing->paid_at ? $billing->paid_at->toIso8601String() : now()->toIso8601String()
        ));

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Pembayaran tunai berhasil diverifikasi.',
                'data' => $billing,
                'cash_received' => $cashReceived,
                'change' => $change,
            ]);
        }

        return redirect()->back()->with('success', 'Pembayaran tunai berhasil. Kembalian: Rp '.number_format($change, 0, ',', '.'));
    }

    /**
     * Generate Dynamic QRIS Code langsung (In-App POS Popup) via Xendit API.
     */
    public function generateQris(int $id): JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $billing = Billing::where('billing_id', $id)->lockForUpdate()->firstOrFail();

        if ($billing->status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Tagihan ini sudah lunas sebelumnya.',
            ], 422);
        }

        // Panggil Dynamic QR Code Service
        $qrData = $this->xenditService->createDynamicQris($billing);

        $billing->update([
            'xendit_id' => $qrData['id'] ?? null,
            'xendit_payment_url' => $qrData['qr_string'] ?? null,
            'payment_method' => 'xendit_qris',
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'QRIS Dinamis berhasil dibuat.',
            'qr_string' => $qrData['qr_string'],
            'amount' => (float) $billing->total_amount,
            'invoice_number' => $billing->invoice_number,
            'billing_id' => $billing->billing_id,
            'billing_status' => $billing->status,
            'xendit_id' => $billing->xendit_id,
        ]);
    }

    /**
     * Endpoint ringan untuk background polling status pembayaran billing (/status).
     */
    public function checkStatus(int $id): JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $billing = Billing::with('processedByNurse')->findOrFail($id);

        return response()->json([
            'status' => true,
            'billing_id' => $billing->billing_id,
            'invoice_number' => $billing->invoice_number,
            'billing_status' => $billing->status,
            'is_paid' => $billing->status === 'paid',
            'payment_method' => $billing->payment_method,
            'paid_at' => $billing->paid_at ? $billing->paid_at->toIso8601String() : null,
            'processed_by' => $billing->processedByNurse?->name,
        ]);
    }

    /**
     * Memproses pelunasan tagihan melalui Mesin EDC (Kartu Debit / Kredit).
     */
    public function payEdc(Request $request, int $id): JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $request->validate([
            'card_type' => ['required', 'string', 'in:Debit,Kredit,debit,kredit'],
            'bank_name' => ['required', 'string', 'max:50'],
            'approval_code' => ['required', 'string', 'max:50'],
            'card_last_four' => ['nullable', 'string', 'max:4'],
        ]);

        $nurse = $request->user()?->nurse;
        $nurseId = $nurse?->nurse_id;
        $cardType = ucfirst(strtolower((string) $request->input('card_type')));
        $bankName = strtoupper((string) $request->input('bank_name'));
        $approvalCode = (string) $request->input('approval_code');
        $cardLastFour = (string) ($request->input('card_last_four') ?? '');

        $billing = DB::transaction(function () use ($id, $nurseId, $cardType, $bankName, $approvalCode, $cardLastFour) {
            $bill = Billing::where('billing_id', $id)->lockForUpdate()->firstOrFail();

            if ($bill->status === 'paid') {
                throw new Exception('Tagihan ini sudah lunas sebelumnya.');
            }

            $methodName = 'EDC '.$cardType.' - '.$bankName.' ('.$approvalCode.($cardLastFour ? ' *'.$cardLastFour : '').')';

            $bill->update([
                'status' => 'paid',
                'payment_method' => $methodName,
                'processed_by_nurse_id' => $nurseId,
                'paid_at' => now(),
            ]);

            // Sinkronisasi status reservasi & resep obat
            if ($bill->reservation) {
                $bill->reservation->update(['status' => 'completed']);

                $prescription = $bill->reservation->medicalRecord?->prescription;
                if ($prescription && in_array($prescription->status, ['menunggu', 'diproses'])) {
                    $prescription->update(['status' => 'selesai']);
                }
            }

            return $bill;
        });

        // Siarkan konfirmasi pembayaran instan ke channel WebSocket private billing
        event(new PaymentSettledEvent(
            billingId: (int) $billing->billing_id,
            invoiceNumber: (string) $billing->invoice_number,
            status: 'paid',
            paymentMethod: (string) $billing->payment_method,
            paidAmount: (float) $billing->total_amount,
            paidAt: $billing->paid_at ? $billing->paid_at->toIso8601String() : now()->toIso8601String()
        ));

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran via Mesin EDC berhasil diverifikasi.',
            'data' => $billing,
        ]);
    }

    /**
     * Memproses permintaan Checkout Online Xendit (Invoice / Web Link).
     */
    public function payXendit(Request $request, int $id): JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $billing = Billing::where('billing_id', $id)->lockForUpdate()->firstOrFail();

        if ($billing->status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Tagihan sudah lunas.',
            ], 422);
        }

        // Buat Invoice di Xendit Gateway
        $xenditInvoice = $this->xenditService->createBillingInvoice($billing);

        $billing->update([
            'xendit_id' => $xenditInvoice['id'] ?? null,
            'xendit_payment_url' => $xenditInvoice['invoice_url'] ?? null,
            'payment_method' => 'xendit_invoice',
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invoice Xendit berhasil digenerate.',
            'xendit_payment_url' => $billing->xendit_payment_url,
            'xendit_id' => $billing->xendit_id,
            'data' => $billing,
        ]);
    }

    /**
     * Data struk kuitansi kasir untuk printer thermal ESC/POS (58mm / 80mm).
     */
    public function printThermalReceipt(Request $request, int $id): JsonResponse
    {
        Gate::authorize('access-pekerja-only');

        $billing = Billing::with([
            'patient.user',
            'reservation.doctorSchedule.doctor.specialization',
            'reservation.doctorSchedule.poli',
            'items',
            'processedByNurse.user',
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Data struk thermal berhasil dimuat.',
            'data' => [
                'hospital' => [
                    'name' => 'Hospital Population',
                    'address' => 'Jl. Kesehatan No. 123, Jakarta',
                    'phone' => '(021) 555-0199',
                    'unit' => 'Instalasi Kasir & Farmasi Rawat Jalan',
                ],
                'billing' => $billing,
            ],
        ]);
    }
}
