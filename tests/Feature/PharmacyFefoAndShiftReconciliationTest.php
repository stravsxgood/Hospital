<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use App\Services\FEFODispensationService;

function setupPharmacyAndCashierEnvironment(): array
{
    $spec = Specialization::firstOrCreate(
        ['code_specialization' => 'SP-GEN'],
        ['name_specialization' => 'Spesialis Umum', 'description' => 'General']
    );

    $poli = Poli::firstOrCreate(
        ['kode_poli' => 'PL-GEN'],
        ['name_poli' => 'Poli Umum', 'location' => 'Lantai 1', 'status' => 'Aktif']
    );

    $room = Room::firstOrCreate(
        ['code_room' => 'RM-101'],
        ['name_room' => 'Ruang 101', 'type_room' => 'Pemeriksaan', 'capacity' => 1, 'floor' => 1]
    );

    $doctorUser = User::factory()->create([
        'name' => 'dr. Anton',
        'email' => 'anton.'.uniqid().'@hospital.test',
        'role' => 'doctor',
    ]);

    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Anton',
        'sip_number' => 'SIP-GEN-'.uniqid(),
        'gender' => 'Laki-laki',
        'number_phone' => '081233445577',
        'join_date' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
        'quota_day' => 40,
        'status' => 'Aktif',
    ]);

    // Permanent Staff Nurse (Pekerja Tetap)
    $nurseUser = User::factory()->create([
        'name' => 'Suster Maya, S.Kep',
        'email' => 'maya.'.uniqid().'@hospital.test',
        'role' => 'nurse',
    ]);

    $nurse = Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Suster Maya, S.Kep',
        'registration_number' => 'STR-NURSE-'.uniqid(),
        'type' => 'tetap',
        'gender' => 'Perempuan',
        'date_start' => now()->toDateString(),
    ]);

    $patientUser = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi.'.uniqid().'@patient.test',
        'role' => 'patient',
    ]);

    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'resident_n' => '3172'.rand(100000000000, 999999999999),
        'name' => 'Budi Santoso',
        'gender' => 'Laki-laki',
        'birthday_date' => '1985-08-20',
        'number_phone' => '081987654321',
        'address' => 'Jl. Kenanga No. 5',
        'registration_date' => now()->toDateString(),
    ]);

    return compact('doctorUser', 'doctor', 'schedule', 'nurseUser', 'nurse', 'patientUser', 'patient');
}

test('fefo dispensation service deducts earliest expiring medicine batches first', function () {
    $env = setupPharmacyAndCashierEnvironment();

    $med = Medicine::create([
        'code_medicine' => 'MED-PAR-01',
        'name_medicine' => 'Paracetamol 500mg Tablet',
        'type' => 'Analgesik',
        'stock' => 50,
        'price' => 2000,
        'unit' => 'Tablet',
    ]);

    // Batch 1: Expire lebih dekat (2 bulan lagi), stok 10
    $batchNear = MedicineBatch::create([
        'medicine_id' => $med->medicine_id,
        'batch_number' => 'BATCH-NEAR-01',
        'stock_quantity' => 10,
        'expiry_date' => now()->addMonths(2)->toDateString(),
        'purchase_price' => 1500,
    ]);

    // Batch 2: Expire lebih lama (12 bulan lagi), stok 40
    $batchFar = MedicineBatch::create([
        'medicine_id' => $med->medicine_id,
        'batch_number' => 'BATCH-FAR-02',
        'stock_quantity' => 40,
        'expiry_date' => now()->addMonths(12)->toDateString(),
        'purchase_price' => 1500,
    ]);

    // Rekam Medis Pasien
    $emr = MedicalRecord::create([
        'patient_id' => $env['patient']->patient_id,
        'doctor_id' => $env['doctor']->doctor_id,
        'subjective' => 'Demam dan sakit kepala',
        'objective' => ['temperature' => 37.5, 'pulse' => 80],
        'assessment' => 'Febris Akut',
        'plan' => 'Istirahat dan antipiretik',
    ]);

    // Resep meminta 15 tablet Paracetamol
    $prescription = Prescription::create([
        'prescription_number' => 'RX-'.strtoupper(uniqid()),
        'medical_record_id' => $emr->medical_record_id,
        'status' => 'menunggu',
    ]);

    $pItem = PrescriptionItem::create([
        'prescription_id' => $prescription->prescription_id,
        'medicine_id' => $med->medicine_id,
        'quantity' => 15,
        'dosage' => '3 x 1 Tablet',
        'instructions' => 'Sesudah makan',
    ]);

    // Eksekusi FEFO Service
    $fefoService = app(FEFODispensationService::class);
    $deductions = $fefoService->dispensePrescription($prescription);

    // Verifikasi Batch 1 (Near) habis (10 -> 0)
    $batchNear->refresh();
    expect($batchNear->stock_quantity)->toBe(0);

    // Verifikasi Batch 2 (Far) berkurang 5 (40 -> 35)
    $batchFar->refresh();
    expect($batchFar->stock_quantity)->toBe(35);

    // Verifikasi Master Medicine Stock berkurang 15 (50 -> 35)
    $med->refresh();
    expect($med->stock)->toBe(35);
});

test('permanent staff can open and close cashier shift with accurate cash reconciliation', function () {
    $env = setupPharmacyAndCashierEnvironment();

    // 1. Buka Shift Pagi dengan modal kas awal Rp 500.000
    $responseOpen = $this->actingAs($env['nurseUser'])
        ->postJson('/staff/cashier-shifts/open', [
            'shift_name' => 'Pagi',
            'opening_cash' => 500000,
            'notes' => 'Pecahan uang kecil siap',
        ]);

    $responseOpen->assertStatus(201)
        ->assertJson([
            'status' => true,
        ]);

    $this->assertDatabaseHas('cashier_shift', [
        'nurse_id' => $env['nurse']->nurse_id,
        'shift_name' => 'Pagi',
        'opening_cash' => 500000,
        'status' => 'open',
    ]);

    // 2. Cek Live Status Shift
    $responseStatus = $this->actingAs($env['nurseUser'])
        ->getJson('/staff/cashier-shifts/current');

    $responseStatus->assertOk()
        ->assertJson([
            'status' => true,
            'has_shift' => true,
        ]);
    expect($responseStatus->json('data.live_stats.expected_cash'))->toBe(500000);

    // 3. Tutup Shift dengan Kas Fisik dihitung Rp 520.000 (Kelebihan Rp 20.000)
    $responseClose = $this->actingAs($env['nurseUser'])
        ->postJson('/staff/cashier-shifts/close', [
            'closing_cash_actual' => 520000,
            'notes' => 'Terdapat kelebihan tip tunai pasien Rp 20.000',
        ]);

    $responseClose->assertOk()
        ->assertJson([
            'status' => true,
        ]);

    $this->assertDatabaseHas('cashier_shift', [
        'nurse_id' => $env['nurse']->nurse_id,
        'status' => 'closed',
        'closing_cash_actual' => 520000,
        'discrepancy' => 20000,
    ]);
});
