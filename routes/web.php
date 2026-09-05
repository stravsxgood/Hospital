<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DisplayVideoController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CashierShiftController;
use App\Http\Controllers\ClinicalAssistantController;
use App\Http\Controllers\ClinicLocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorConsultationController;
use App\Http\Controllers\DoctorQueueController;
use App\Http\Controllers\DoctorSchedulePageController;
use App\Http\Controllers\DoctorSupervisionController;
use App\Http\Controllers\KoasLogbookController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\PatientStoryController;
use App\Http\Controllers\PoliTeamController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDisplayController;
use App\Http\Controllers\SatuSehatController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\StaffActionController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\XenditWebhookController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Webhook Publik Pembayaran Xendit (CSRF-exempted di bootstrap/app.php)
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handle'])->name('webhooks.xendit');

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
    // Manajemen Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
    Route::middleware(['role:staff,nurse,doctor,admin,super-admin,staff-pekerja,koas-intern'])->group(function () {
        Route::get('/staff', [StaffDashboardController::class, 'index'])->name('staff');
        Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');

        // Aksi Meja Depan: Konfirmasi Kedatangan Pasien (Check-in)
        Route::post('/staff/reservations/{id}/confirm-arrival', [StaffActionController::class, 'confirmArrival'])->name('staff.reservations.confirm-arrival');

        // Aksi Farmasi: Pemenuhan Resep & Peracikan Obat
        Route::post('/staff/prescriptions/{id}/process', [StaffActionController::class, 'processPrescription'])->name('staff.prescriptions.process')->middleware('can:access-pekerja-only');
        Route::post('/staff/prescriptions/{id}/complete', [StaffActionController::class, 'completePrescription'])->name('staff.prescriptions.complete')->middleware('can:access-pekerja-only');

        // Cetak Dokumen Medis Klinis (Akses Bersama: Staf Tetap & Koas)
        Route::get('/staff/print/medical-resume/{id}', [MedicalDocumentController::class, 'printResume'])->name('staff.print.resume');
        Route::get('/staff/print/sick-letter/{id}', [MedicalDocumentController::class, 'printSickLetter'])->name('staff.print.sick-letter');
        Route::get('/staff/print/referral-letter/{id}', [MedicalDocumentController::class, 'printReferral'])->name('staff.print.referral');

        // Modul Khusus Staf / Perawat Tetap (Pekerja): Kasir & Billing POS + Inventori Obat
        Route::middleware(['can:access-pekerja-only'])->group(function () {
            // Kasir & Billing POS
            Route::get('/staff/billing', [BillingController::class, 'index'])->name('staff.billing.index');
            Route::post('/staff/billing', [BillingController::class, 'store'])->name('staff.billing.store');
            Route::get('/staff/billing/calculate/{appointment_id}', [BillingController::class, 'calculateAmount'])->name('staff.billing.calculate');
            Route::get('/staff/billing/{id}', [BillingController::class, 'show'])->name('staff.billing.show');
            Route::post('/staff/billing/create-from-reservation/{reservation_id}', [BillingController::class, 'createFromReservation'])->name('staff.billing.create-from-reservation');
            Route::post('/staff/billing/{id}/pay-cash', [BillingController::class, 'payCash'])->name('staff.billing.pay-cash');
            Route::post('/staff/billing/{id}/pay-qris', [BillingController::class, 'generateQris'])->name('staff.billing.pay-qris');
            Route::post('/staff/billing/{id}/pay-edc', [BillingController::class, 'payEdc'])->name('staff.billing.pay-edc');
            Route::post('/staff/billing/{id}/pay-xendit', [BillingController::class, 'payXendit'])->name('staff.billing.pay-xendit');
            Route::get('/staff/billing/{id}/status', [BillingController::class, 'checkStatus'])->name('staff.billing.status');
            Route::get('/staff/billing/{id}/print-receipt', [MedicalDocumentController::class, 'printReceipt'])->name('staff.billing.print-receipt');

            // Master Data & Inventori Obat Farmasi
            Route::get('/staff/medicines', [MedicineController::class, 'index'])->name('staff.medicines.index');
            Route::post('/staff/medicines', [MedicineController::class, 'store'])->name('staff.medicines.store');
            Route::put('/staff/medicines/{id}', [MedicineController::class, 'update'])->name('staff.medicines.update');
            Route::delete('/staff/medicines/{id}', [MedicineController::class, 'destroy'])->name('staff.medicines.destroy');
            Route::post('/staff/medicines/{id}/adjust-stock', [MedicineController::class, 'adjustStock'])->name('staff.medicines.adjust-stock');

            // Sesi & Rekonsiliasi Shift Kasir (Cashier Shift)
            Route::get('/staff/cashier-shifts/current', [CashierShiftController::class, 'currentShift'])->name('staff.cashier-shifts.current');
            Route::post('/staff/cashier-shifts/open', [CashierShiftController::class, 'openShift'])->name('staff.cashier-shifts.open');
            Route::post('/staff/cashier-shifts/close', [CashierShiftController::class, 'closeShift'])->name('staff.cashier-shifts.close');
            Route::get('/staff/cashier-shifts/{id}/print-summary', [CashierShiftController::class, 'printSummary'])->name('staff.cashier-shifts.print-summary');
            Route::get('/staff/billing/{id}/print-thermal', [BillingController::class, 'printThermalReceipt'])->name('staff.billing.print-thermal');

            // Audit Trail Akses Rekam Medis (UU PDP Compliance)
            Route::get('/staff/audit-logs', [AuditLogController::class, 'index'])->name('staff.audit-logs.index');
        });
    });

    // Panel Antrean Dokter & Konsultasi Medis (EMR & Resep)
    Route::middleware(['role:doctor,admin,super-admin'])->group(function () {
        Route::get('/doctor/queue', [DoctorQueueController::class, 'index'])->name('doctor.queue.index');
        Route::patch('/doctor/queue/{appointment}/call', [DoctorQueueController::class, 'callPatient'])->name('doctor.queue.call');
        Route::patch('/doctor/queue/{appointment}/complete', [DoctorQueueController::class, 'completeConsultation'])->name('doctor.queue.complete');
        Route::patch('/doctor/queue/{appointment}/skip', [DoctorQueueController::class, 'skipPatient'])->name('doctor.queue.skip');

        // Portal Supervisi Klinis DPJP untuk Mahasiswa Koas
        Route::get('/doctor/supervision', [DoctorSupervisionController::class, 'index'])->name('doctor.supervision.index');
        Route::get('/doctor/supervision/{id}', [DoctorSupervisionController::class, 'show'])->name('doctor.supervision.show');
        Route::post('/doctor/supervision/{id}/review', [DoctorSupervisionController::class, 'review'])->name('doctor.supervision.review');

        // Rekam Medis (SOAP), Resep Elektronik, & Riwayat Klinis Pasien
        Route::post('/doctor/consultations', [DoctorConsultationController::class, 'store'])->name('doctor.consultations.store');
        Route::get('/doctor/medicines', [DoctorConsultationController::class, 'getMedicines'])->name('doctor.medicines.index');
    });

    // Riwayat Rekam Medis Pasien (Diotorisasi di Controller: Tenaga Medis atau Pemilik Rekam Medis)
    Route::get('/doctor/patients/{patient_id}/history', [DoctorConsultationController::class, 'getPatientHistory'])->name('doctor.patients.history');

    // Logbook Klinis Digital Mahasiswa Koas (Dokter Muda / Intern)
    Route::middleware(['role:nurse,koas,koas-intern,admin,super-admin'])->group(function () {
        Route::get('/koas/logbook', [KoasLogbookController::class, 'index'])->name('koas.logbook.index');
        Route::post('/koas/logbook', [KoasLogbookController::class, 'store'])->name('koas.logbook.store');
        Route::put('/koas/logbook/{id}', [KoasLogbookController::class, 'update'])->name('koas.logbook.update');
        Route::post('/koas/logbook/{id}/submit', [KoasLogbookController::class, 'submitForReview'])->name('koas.logbook.submit');
        Route::delete('/koas/logbook/{id}', [KoasLogbookController::class, 'destroy'])->name('koas.logbook.destroy');
    });

    // Asisten Klinis Dokter: Autocomplete ICD-10, Template SOAP, & Evaluasi Keamanan Resep
    Route::middleware(['role:doctor,nurse,staff,admin,super-admin,staff-pekerja,koas-intern'])->group(function () {
        Route::get('/api/clinical/icd10', [ClinicalAssistantController::class, 'searchIcd10'])->name('clinical.icd10');
        Route::get('/api/clinical/soap-templates', [ClinicalAssistantController::class, 'getSoapTemplates'])->name('clinical.soap-templates.index');
        Route::post('/api/clinical/soap-templates', [ClinicalAssistantController::class, 'storeSoapTemplate'])->name('clinical.soap-templates.store');
        Route::post('/api/clinical/safety-check', [ClinicalAssistantController::class, 'checkSafety'])->name('clinical.safety-check');
        Route::post('/api/clinical/check-safety', [ClinicalAssistantController::class, 'checkSafety'])->name('clinical.check-safety');
    });

    // SatuSehat Kemenkes FHIR R4 Bundle Transformer Endpoint
    Route::get('/api/satusehat/records/{medical_record_id}/fhir-bundle', [SatuSehatController::class, 'getFhirBundle'])->name('satusehat.fhir-bundle');

    // =========================================================================
    // SUPER ADMIN GOVERNANCE & OPERATIONAL MANAGEMENT
    // =========================================================================
    Route::middleware(['role:super-admin|admin|Super Admin|Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Manajemen Pengguna & Provisioning Tenaga Medis
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/doctors', [AdminUserController::class, 'storeDoctor'])->name('users.doctors.store');
        Route::post('/users/nurses', [AdminUserController::class, 'storeNurse'])->name('users.nurses.store');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::delete('/users/{user}/force', [AdminUserController::class, 'forceDestroy'])->name('users.force-destroy')->withTrashed();

        // Master Fasilitas Poliklinik & Ruangan
        Route::resource('polis', AdminPoliController::class)->except(['create', 'edit']);
        Route::resource('schedules', AdminScheduleController::class)->except(['create', 'edit']);

        // Global Regulatory Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

        // Pengaturan Sistem Dinamis (DisplayBoard, Operasional)
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

        // Manajemen Playlist Video Display TV Antrean
        Route::post('/display-videos', [DisplayVideoController::class, 'store'])->name('display-videos.store');
        Route::patch('/display-videos/{displayVideo}/toggle', [DisplayVideoController::class, 'toggle'])->name('display-videos.toggle');
        Route::delete('/display-videos/{displayVideo}', [DisplayVideoController::class, 'destroy'])->name('display-videos.destroy');
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
