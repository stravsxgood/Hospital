<?php

use App\Http\Controllers\ClinicLocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorSchedulePageController;
use App\Http\Controllers\PatientStoryController;
use App\Http\Controllers\PoliTeamController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Http\Controllers\PublicDisplayController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorConsultationController;
use App\Http\Controllers\DoctorQueueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\DoctorSchedule;

Route::get('/', function () {
    $schedules = DoctorSchedule::with(['doctor.specialization', 'poli', 'room'])
        ->where('status', 'Aktif')
        ->get();

    return Inertia::render('Welcome', [
        'schedules' => $schedules,
    ]);
})->name('home');

// Rute Publik Layar Monitor TV Ruang Tunggu
Route::get('/display', [PublicDisplayController::class, 'index'])->name('display.index');
Route::get('/display/live-data', [PublicDisplayController::class, 'liveData'])->name('display.live');

// Rute Publik Jadwal Dokter (Tamu & Pasien)
Route::get('/schedule-guest', DoctorSchedulePageController::class)->name('schedule-guest');

// Rute Publik Cerita Pasien (Patient Stories)
Route::get('/patient-story', [PatientStoryController::class, 'index'])->name('patient.story');

// Rute Publik Layanan & Sub-Spesialisasi Medis (Evergreen Theme)
Route::get('/specializations', [SpecializationController::class, 'index'])->name('specializations.index');

// Rute Publik Tim Medis & Fasilitas Poliklinik (/teams)
Route::get('/teams', [PoliTeamController::class, 'index'])->name('teams.poli.index');

// Rute Publik Direktori Lokasi Klinik & Jaringan Rumah Sakit (/clinic-location)
Route::get('/clinic-location', [ClinicLocationController::class, 'index'])->name('clinic.location');
// ────────────────────────────────────────────────────────────────────────────
// PENTING: Grup rute eksplisit (auth) HARUS didefinisikan SEBELUM rute
// wildcard {current_team} di bawahnya. Jika tidak, Laravel akan mencocokkan
// /patient/dashboard sebagai {current_team}=patient → dashboard,
// sehingga middleware EnsureTeamMembership men-trigger abort(403).
// ────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    // Dashboard Khusus Pasien
    Route::get('/patient/dashboard', [PatientDashboardController::class, 'index'])->name('patient.dashboard');

    Route::get('/doctor-schedules', DoctorSchedulePageController::class)->name('doctor.schedules');

    Route::get('/my-appointments', [AppointmentController::class, 'index'])->name('my');
    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('appointments.store');
        
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
    // Workspace Staf & Perawat Terpadu
    Route::get('/staff', [StaffDashboardController::class, 'index'])->name('staff');
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');

    // Aksi Meja Depan: Konfirmasi Kedatangan Pasien (Check-in)
    Route::post('/staff/reservations/{id}/confirm-arrival', [\App\Http\Controllers\StaffActionController::class, 'confirmArrival'])->name('staff.reservations.confirm-arrival');

    // Aksi Farmasi: Pemenuhan Resep & Peracikan Obat
    Route::post('/staff/prescriptions/{id}/process', [\App\Http\Controllers\StaffActionController::class, 'processPrescription'])->name('staff.prescriptions.process')->middleware('can:access-pekerja-only');
    Route::post('/staff/prescriptions/{id}/complete', [\App\Http\Controllers\StaffActionController::class, 'completePrescription'])->name('staff.prescriptions.complete')->middleware('can:access-pekerja-only');

    // Cetak Dokumen Medis Klinis (Akses Bersama: Staf Tetap & Koas)
    Route::get('/staff/print/medical-resume/{id}', [\App\Http\Controllers\MedicalDocumentController::class, 'printResume'])->name('staff.print.resume');
    Route::get('/staff/print/sick-letter/{id}', [\App\Http\Controllers\MedicalDocumentController::class, 'printSickLetter'])->name('staff.print.sick-letter');
    Route::get('/staff/print/referral-letter/{id}', [\App\Http\Controllers\MedicalDocumentController::class, 'printReferral'])->name('staff.print.referral');

    // Modul Khusus Staf / Perawat Tetap (Pekerja): Kasir & Billing POS + Inventori Obat
    Route::middleware(['can:access-pekerja-only'])->group(function () {
        // Kasir & Billing POS
        Route::get('/staff/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('staff.billing.index');
        Route::get('/staff/billing/{id}', [\App\Http\Controllers\BillingController::class, 'show'])->name('staff.billing.show');
        Route::post('/staff/billing/create-from-reservation/{reservation_id}', [\App\Http\Controllers\BillingController::class, 'createFromReservation'])->name('staff.billing.create-from-reservation');
        Route::post('/staff/billing/{id}/pay-cash', [\App\Http\Controllers\BillingController::class, 'payCash'])->name('staff.billing.pay-cash');
        Route::post('/staff/billing/{id}/pay-qris', [\App\Http\Controllers\BillingController::class, 'generateQris'])->name('staff.billing.pay-qris');
        Route::post('/staff/billing/{id}/pay-edc', [\App\Http\Controllers\BillingController::class, 'payEdc'])->name('staff.billing.pay-edc');
        Route::post('/staff/billing/{id}/pay-xendit', [\App\Http\Controllers\BillingController::class, 'payXendit'])->name('staff.billing.pay-xendit');
        Route::get('/staff/billing/{id}/status', [\App\Http\Controllers\BillingController::class, 'checkStatus'])->name('staff.billing.status');
        Route::get('/staff/billing/{id}/print-receipt', [\App\Http\Controllers\MedicalDocumentController::class, 'printReceipt'])->name('staff.billing.print-receipt');

        // Master Data & Inventori Obat Farmasi
        Route::get('/staff/medicines', [\App\Http\Controllers\MedicineController::class, 'index'])->name('staff.medicines.index');
        Route::post('/staff/medicines', [\App\Http\Controllers\MedicineController::class, 'store'])->name('staff.medicines.store');
        Route::put('/staff/medicines/{id}', [\App\Http\Controllers\MedicineController::class, 'update'])->name('staff.medicines.update');
        Route::delete('/staff/medicines/{id}', [\App\Http\Controllers\MedicineController::class, 'destroy'])->name('staff.medicines.destroy');
        Route::post('/staff/medicines/{id}/adjust-stock', [\App\Http\Controllers\MedicineController::class, 'adjustStock'])->name('staff.medicines.adjust-stock');

        // Sesi & Rekonsiliasi Shift Kasir (Cashier Shift)
        Route::get('/staff/cashier-shifts/current', [\App\Http\Controllers\CashierShiftController::class, 'currentShift'])->name('staff.cashier-shifts.current');
        Route::post('/staff/cashier-shifts/open', [\App\Http\Controllers\CashierShiftController::class, 'openShift'])->name('staff.cashier-shifts.open');
        Route::post('/staff/cashier-shifts/close', [\App\Http\Controllers\CashierShiftController::class, 'closeShift'])->name('staff.cashier-shifts.close');
        Route::get('/staff/cashier-shifts/{id}/print-summary', [\App\Http\Controllers\CashierShiftController::class, 'printSummary'])->name('staff.cashier-shifts.print-summary');
        Route::get('/staff/billing/{id}/print-thermal', [\App\Http\Controllers\BillingController::class, 'printThermalReceipt'])->name('staff.billing.print-thermal');

        // Audit Trail Akses Rekam Medis (UU PDP Compliance)
        Route::get('/staff/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('staff.audit-logs.index');
    });

    // Panel Antrean Dokter & Konsultasi Medis (EMR & Resep)
    Route::get('/doctor/queue', [DoctorQueueController::class, 'index'])->name('doctor.queue.index');
    Route::patch('/doctor/queue/{appointment}/call', [DoctorQueueController::class, 'callPatient'])->name('doctor.queue.call');
    Route::patch('/doctor/queue/{appointment}/complete', [DoctorQueueController::class, 'completeConsultation'])->name('doctor.queue.complete');
    Route::patch('/doctor/queue/{appointment}/skip', [DoctorQueueController::class, 'skipPatient'])->name('doctor.queue.skip');

    // Portal Supervisi Klinis DPJP untuk Mahasiswa Koas
    Route::get('/doctor/supervision', [\App\Http\Controllers\DoctorSupervisionController::class, 'index'])->name('doctor.supervision.index');
    Route::get('/doctor/supervision/{id}', [\App\Http\Controllers\DoctorSupervisionController::class, 'show'])->name('doctor.supervision.show');
    Route::post('/doctor/supervision/{id}/review', [\App\Http\Controllers\DoctorSupervisionController::class, 'review'])->name('doctor.supervision.review');

    // Logbook Klinis Digital Mahasiswa Koas (Dokter Muda / Intern)
    Route::get('/koas/logbook', [\App\Http\Controllers\KoasLogbookController::class, 'index'])->name('koas.logbook.index');
    Route::post('/koas/logbook', [\App\Http\Controllers\KoasLogbookController::class, 'store'])->name('koas.logbook.store');
    Route::put('/koas/logbook/{id}', [\App\Http\Controllers\KoasLogbookController::class, 'update'])->name('koas.logbook.update');
    Route::post('/koas/logbook/{id}/submit', [\App\Http\Controllers\KoasLogbookController::class, 'submitForReview'])->name('koas.logbook.submit');
    Route::delete('/koas/logbook/{id}', [\App\Http\Controllers\KoasLogbookController::class, 'destroy'])->name('koas.logbook.destroy');

    // Rekam Medis (SOAP), Resep Elektronik, & Riwayat Klinis Pasien
    Route::post('/doctor/consultations', [DoctorConsultationController::class, 'store'])->name('doctor.consultations.store');
    Route::get('/doctor/patients/{patient_id}/history', [DoctorConsultationController::class, 'getPatientHistory'])->name('doctor.patients.history');
    Route::get('/doctor/medicines', [DoctorConsultationController::class, 'getMedicines'])->name('doctor.medicines.index');

    // Asisten Klinis Dokter: Autocomplete ICD-10, Template SOAP, & Evaluasi Keamanan Resep
    Route::get('/api/clinical/icd10', [\App\Http\Controllers\ClinicalAssistantController::class, 'searchIcd10'])->name('clinical.icd10');
    Route::get('/api/clinical/soap-templates', [\App\Http\Controllers\ClinicalAssistantController::class, 'getSoapTemplates'])->name('clinical.soap-templates.index');
    Route::post('/api/clinical/soap-templates', [\App\Http\Controllers\ClinicalAssistantController::class, 'storeSoapTemplate'])->name('clinical.soap-templates.store');
    Route::post('/api/clinical/safety-check', [\App\Http\Controllers\ClinicalAssistantController::class, 'checkSafety'])->name('clinical.safety-check');
    Route::post('/api/clinical/check-safety', [\App\Http\Controllers\ClinicalAssistantController::class, 'checkSafety'])->name('clinical.check-safety');

    // SatuSehat Kemenkes FHIR R4 Bundle Transformer Endpoint
    Route::get('/api/satusehat/records/{medical_record_id}/fhir-bundle', [\App\Http\Controllers\SatuSehatController::class, 'getFhirBundle'])->name('satusehat.fhir-bundle');

    // =========================================================================
    // SUPER ADMIN GOVERNANCE & OPERATIONAL MANAGEMENT
    // =========================================================================
    Route::middleware(['role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Manajemen Pengguna & Provisioning Tenaga Medis
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/doctors', [\App\Http\Controllers\Admin\AdminUserController::class, 'storeDoctor'])->name('users.doctors.store');
        Route::post('/users/nurses', [\App\Http\Controllers\Admin\AdminUserController::class, 'storeNurse'])->name('users.nurses.store');
        Route::patch('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Master Fasilitas Poliklinik & Ruangan
        Route::resource('polis', \App\Http\Controllers\Admin\AdminPoliController::class)->except(['create', 'edit']);
        Route::resource('schedules', \App\Http\Controllers\Admin\AdminScheduleController::class)->except(['create', 'edit']);

        // Global Regulatory Audit Logs
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

// Rute wildcard tim — HARUS di bawah semua rute eksplisit agar tidak
// menangkap path seperti /patient/..., /staff, /doctor/..., dll.
Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        // Tambahkan rute internal manajemen rekam medis/antrean di sini
    });

require __DIR__.'/settings.php';
