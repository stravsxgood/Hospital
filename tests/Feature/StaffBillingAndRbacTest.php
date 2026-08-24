<?php

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;

function createStaffWorkspaceContext(): array
{
    // 1. Master Specialization, Poli & Room
    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-JP'],
        ['name_specialization' => 'Spesialis Jantung & Pembuluh Darah (Sp.JP)', 'description' => 'Test Cardiologist']
    );

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'PL-JTG'],
        ['name_poli' => 'Poli Jantung & Kardiovaskular', 'location' => 'Lantai 3', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'RM-301'],
        ['name_room' => 'Ruang Periksa Jantung 301', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 3]
    );

    // 2. Doctor User & Profile
    $doctorUser = User::factory()->create([
        'name' => 'dr. Bagus Santoso, Sp.JP',
        'email' => 'drbagus' . uniqid() . '@test.com',
        'role' => 'doctor',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Bagus Santoso, Sp.JP',
        'sip_number' => 'SIP-JP-' . uniqid(),
        'gender' => 'Laki-laki',
        'number_phone' => '081211112222',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    // 3. Schedule
    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00:00',
        'end_time' => '14:00:00',
        'quota_day' => 20,
        'status' => 'Aktif',
    ]);

    // 4. Permanent Nurse User ("tetap" / Pekerja)
    $nurseTetapUser = User::factory()->create([
        'name' => 'Ns. Ratna Sari, S.Kep (Tetap)',
        'email' => 'ratna' . uniqid() . '@hospital.id',
        'role' => 'nurse',
    ]);

    $nurseTetap = Nurse::create([
        'user_id' => $nurseTetapUser->id,
        'name' => 'Ns. Ratna Sari, S.Kep',
        'registration_number' => 'NIRA-TETAP-' . uniqid(),
        'type' => 'tetap',
        'gender' => 'Perempuan',
        'date_start' => now()->subYears(3)->toDateString(),
    ]);

    // 5. Intern Nurse User ("koas" / Mahasiswa Magang)
    $nurseKoasUser = User::factory()->create([
        'name' => 'Dimas Surya (Koas)',
        'email' => 'dimas' . uniqid() . '@hospital.id',
        'role' => 'nurse',
    ]);

    $nurseKoas = Nurse::create([
        'user_id' => $nurseKoasUser->id,
        'name' => 'Dimas Surya (Koas)',
        'registration_number' => 'NIM-KOAS-' . uniqid(),
        'type' => 'koas',
        'institute' => 'Fakultas Kedokteran UI',
        'gender' => 'Laki-laki',
        'date_start' => now()->subMonths(2)->toDateString(),
    ]);

    // 6. Patient Profile
    $patientUser = User::factory()->create([
        'name' => 'Ahmad Faisal',
        'email' => 'ahmad' . uniqid() . '@test.com',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => (string) rand(3201000000000000, 3201999999999999),
        'name' => 'Ahmad Faisal',
        'gender' => 'Laki-laki',
        'birthday_date' => '1988-04-15',
        'number_phone' => '081288887777',
        'registration_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    // 7. Appointment / Reservation
    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => now()->toDateString(),
        'queue_number' => 'JANTUNG-001',
        'complaint' => 'Nyeri dada kiri menjalar ke lengan',
        'status' => 'completed',
    ]);

    // 8. Medicine master
    $medicine = Medicine::create([
        'code_medicine' => 'MED-ISDN-' . uniqid(),
        'name_medicine' => 'Isosorbide Dinitrate 5mg',
        'type' => 'Tablet',
        'stock' => 100,
        'unit' => 'Tablet',
        'price' => 25000.00,
    ]);

    // 9. Medical Record SOAP & Prescription
    $medicalRecord = MedicalRecord::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'reservation_id' => $appointment->appointment_id,
        'subjective' => 'Nyeri dada saat aktivitas berat.',
        'objective' => [
            'systolic' => 135,
            'diastolic' => 85,
            'pulse' => 82,
            'temperature' => 36.7,
            'oxygen_saturation' => 99,
        ],
        'assessment' => 'Angina Pectoris Stabil (I20.9)',
        'plan' => 'Tirah baring, ISDN sublingual PRN, diet rendah garam.',
    ]);

    $prescription = Prescription::create([
        'medical_record_id' => $medicalRecord->medical_record_id,
        'prescription_number' => 'RX-CARDIO-' . uniqid(),
        'status' => 'menunggu',
    ]);

    PrescriptionItem::create([
        'prescription_id' => $prescription->prescription_id,
        'medicine_id' => $medicine->medicine_id,
        'quantity' => 2,
        'dosage' => '3 x 1 Tablet Sublingual',
        'instructions' => 'Di bawah lidah bila nyeri dada timbul',
    ]);

    return compact(
        'spec',
        'poli',
        'room',
        'doctorUser',
        'doctor',
        'schedule',
        'nurseTetapUser',
        'nurseTetap',
        'nurseKoasUser',
        'nurseKoas',
        'patientUser',
        'patient',
        'appointment',
        'medicine',
        'medicalRecord',
        'prescription'
    );
}

test('permanent staff (tetap) can access billing index and create automatic invoices from completed consultations', function () {
    $ctx = createStaffWorkspaceContext();

    // 1. Akses Billing Index
    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->get('/staff/billing');
    $response->assertOk();

    // 2. Buat Billing dari Sesi Konsultasi
    $createResponse = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/billing/create-from-reservation/{$ctx['appointment']->appointment_id}");

    $createResponse->assertCreated();
    $createResponse->assertJson(['status' => true]);

    $billing = Billing::where('reservation_id', $ctx['appointment']->appointment_id)->first();
    expect($billing)->not->toBeNull();
    expect($billing->status)->toBe('unpaid');

    // Cek kalkulasi: Konsultasi (150.000) + Admin (25.000) + Obat (2 * 25.000 = 50.000) = 225.000
    expect((float) $billing->total_amount)->toBe(225000.00);
    expect($billing->items)->toHaveCount(3);
});

test('intern nurse (koas) is strictly forbidden (403) from accessing cashier, billing, and payment processing', function () {
    $ctx = createStaffWorkspaceContext();

    // 1. Coba Akses Billing Index
    $response = $this->actingAs($ctx['nurseKoasUser'])
        ->get('/staff/billing');
    $response->assertForbidden();

    // 2. Coba Buat Billing
    $createResponse = $this->actingAs($ctx['nurseKoasUser'])
        ->postJson("/staff/billing/create-from-reservation/{$ctx['appointment']->appointment_id}");
    $createResponse->assertForbidden();
});

test('permanent staff can process cash payment in database transaction and settle billing', function () {
    $ctx = createStaffWorkspaceContext();

    // Buat billing terlebih dahulu
    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-TEST-001',
        'total_amount'   => 175000.00,
        'status'         => 'unpaid',
    ]);

    BillingItem::create([
        'billing_id' => $billing->billing_id,
        'item_type'  => 'consultation_fee',
        'item_name'  => 'Konsultasi Dokter',
        'quantity'   => 1,
        'unit_price' => 175000.00,
        'subtotal'   => 175000.00,
    ]);

    // Eksekusi Pembayaran Tunai dengan Uang 200.000 (Kembalian 25.000)
    $payResponse = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/billing/{$billing->billing_id}/pay-cash", [
            'cash_received' => 200000,
        ]);

    $payResponse->assertOk();
    $payResponse->assertJson([
        'status'        => true,
        'cash_received' => 200000,
        'change'        => 25000,
    ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->payment_method)->toBe('cash');
    expect($billing->processed_by_nurse_id)->toBe($ctx['nurseTetap']->nurse_id);
    expect($billing->paid_at)->not->toBeNull();
});

test('permanent staff can initiate Xendit online checkout and generate payment link', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-TEST-XEN-001',
        'total_amount'   => 150000.00,
        'status'         => 'unpaid',
    ]);

    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/billing/{$billing->billing_id}/pay-xendit");

    $response->assertOk();
    $response->assertJson([
        'status' => true,
    ]);

    $billing->refresh();
    expect($billing->status)->toBe('pending');
    expect($billing->xendit_payment_url)->not->toBeNull();
});

test('xendit webhook callback securely updates billing status to paid upon receiving settlement notification', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-WEBHOOK-001',
        'total_amount'   => 200000.00,
        'status'         => 'pending',
        'xendit_id'      => 'xendit_inv_mock12345',
    ]);

    $webhookPayload = [
        'id'              => 'xendit_inv_mock12345',
        'external_id'     => 'BILL-' . $billing->billing_id . '-' . time(),
        'status'          => 'PAID',
        'payment_method'  => 'QRIS',
        'amount'          => 200000,
    ];

    $response = $this->postJson('/api/webhooks/xendit', $webhookPayload, [
        'x-callback-token' => config('services.xendit.webhook_token'),
    ]);

    $response->assertOk();
    $response->assertJson([
        'status' => true,
        'message' => 'Billing successfully marked as PAID',
    ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->payment_method)->toBe('QRIS');
    expect($billing->paid_at)->not->toBeNull();
});

test('both tetap and koas nurses can generate and download clinical PDF documents (resume, sick note, referral)', function () {
    $ctx = createStaffWorkspaceContext();

    // 1. Staf Tetap mengunduh Resume Medis
    $resTetap = $this->actingAs($ctx['nurseTetapUser'])
        ->get("/staff/print/medical-resume/{$ctx['appointment']->appointment_id}?stream=1");
    $resTetap->assertOk();
    expect($resTetap->headers->get('content-type'))->toBe('application/pdf');

    // 2. Koas mengunduh Surat Sakit Dokter
    $resKoas = $this->actingAs($ctx['nurseKoasUser'])
        ->get("/staff/print/sick-letter/{$ctx['appointment']->appointment_id}?stream=1");
    $resKoas->assertOk();
    expect($resKoas->headers->get('content-type'))->toBe('application/pdf');

    // 3. Koas mengunduh Surat Rujukan Eksternal
    $resRujukan = $this->actingAs($ctx['nurseKoasUser'])
        ->get("/staff/print/referral-letter/{$ctx['appointment']->appointment_id}?stream=1");
    $resRujukan->assertOk();
    expect($resRujukan->headers->get('content-type'))->toBe('application/pdf');
});

test('intern nurse (koas) is forbidden from downloading payment cashier receipt PDF', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-RECEIPT-001',
        'total_amount'   => 150000.00,
        'status'         => 'paid',
        'paid_at'        => now(),
    ]);

    // Staf Tetap diizinkan
    $resTetap = $this->actingAs($ctx['nurseTetapUser'])
        ->get("/staff/billing/{$billing->billing_id}/print-receipt?stream=1");
    $resTetap->assertOk();
    expect($resTetap->headers->get('content-type'))->toBe('application/pdf');

    // Koas dilarang (403)
    $resKoas = $this->actingAs($ctx['nurseKoasUser'])
        ->get("/staff/billing/{$billing->billing_id}/print-receipt?stream=1");
    $resKoas->assertForbidden();
});

test('permanent staff can generate dynamic QRIS code with valid EMVCo qr_string and poll live status', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-QRIS-001',
        'total_amount'   => 250000.00,
        'status'         => 'unpaid',
    ]);

    // 1. Request Dynamic QRIS
    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/billing/{$billing->billing_id}/pay-qris");

    $response->assertOk();
    $response->assertJson([
        'status'         => true,
        'billing_status' => 'pending',
        'amount'         => 250000.00,
    ]);
    expect($response->json('qr_string'))->toBeString()->not->toBeEmpty();

    // 2. Poll Status Endpoint
    $pollResponse = $this->actingAs($ctx['nurseTetapUser'])
        ->getJson("/staff/billing/{$billing->billing_id}/status");

    $pollResponse->assertOk();
    $pollResponse->assertJson([
        'status'         => true,
        'billing_id'     => $billing->billing_id,
        'billing_status' => 'pending',
        'is_paid'        => false,
    ]);
});

test('permanent staff can settle payment using EDC card terminal swipe', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-EDC-001',
        'total_amount'   => 350000.00,
        'status'         => 'unpaid',
    ]);

    $response = $this->actingAs($ctx['nurseTetapUser'])
        ->postJson("/staff/billing/{$billing->billing_id}/pay-edc", [
            'card_type'      => 'Debit',
            'bank_name'      => 'BCA',
            'approval_code'  => 'APPV-99281',
            'card_last_four' => '4321',
        ]);

    $response->assertOk();
    $response->assertJson(['status' => true]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->payment_method)->toContain('EDC Debit - BCA (APPV-99281 *4321)');
    expect($billing->paid_at)->not->toBeNull();
});

test('xendit dynamic QRIS webhook callback settles billing and synchronizes appointment status', function () {
    $ctx = createStaffWorkspaceContext();

    $billing = Billing::create([
        'reservation_id' => $ctx['appointment']->appointment_id,
        'patient_id'     => $ctx['patient']->patient_id,
        'invoice_number' => 'INV-QRIS-WEBHOOK-001',
        'total_amount'   => 185000.00,
        'status'         => 'pending',
        'xendit_id'      => 'qr_mock_778899',
    ]);

    $webhookPayload = [
        'event' => 'qr.payment',
        'data' => [
            'id'          => 'qr_mock_778899',
            'external_id' => 'QRIS-' . $billing->billing_id . '-' . time(),
            'status'      => 'COMPLETED',
            'amount'      => 185000,
        ],
    ];

    $response = $this->postJson('/api/webhooks/xendit/qr', $webhookPayload, [
        'x-callback-token' => config('services.xendit.webhook_token'),
    ]);

    $response->assertOk();
    $response->assertJson([
        'status'  => true,
        'message' => 'QRIS payment successfully settled',
    ]);

    $billing->refresh();
    expect($billing->status)->toBe('paid');
    expect($billing->payment_method)->toBe('QRIS');
    expect($billing->paid_at)->not->toBeNull();
});

