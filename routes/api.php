<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorConsultationController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\NurseQueueController;
use App\Http\Controllers\Api\PatientRegistrationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\XenditWebhookController;
use App\Http\Middleware\VerifyXenditWebhook;
use Illuminate\Support\Facades\Route;

// Public Endpoints with Rate Limiting
Route::post('/register', [AuthController::class, 'registerPatient'])
    ->middleware('throttle:6,1');

// Tambahkan middleware 'web' agar sesi browser aktif saat login via API + rate limiting
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(['web', 'throttle:10,1']);

// 1. Endpoint Webhook Publik dengan Verifikasi Callback Token Xendit
Route::post('/xendit/webhook', [PaymentController::class, 'handleWebhook'])
    ->middleware(VerifyXenditWebhook::class);
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handle'])
    ->middleware(VerifyXenditWebhook::class);
Route::post('/webhooks/xendit/qr', [XenditWebhookController::class, 'handleQrCallback'])
    ->middleware(VerifyXenditWebhook::class);

// 2. Endpoint Publik Jadwal Dokter (Hanya Read-Only)
Route::get('doctor-schedules', [DoctorScheduleController::class, 'index']);
Route::get('doctor-schedules/{id}', [DoctorScheduleController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    // Mutasi Jadwal Dokter (Hanya Staf Medis & Admin yang Terotentikasi)
    Route::middleware('role:super-admin,admin,staff,nurse,doctor')->group(function () {
        Route::post('doctor-schedules', [DoctorScheduleController::class, 'store']);
        Route::put('doctor-schedules/{id}', [DoctorScheduleController::class, 'update']);
        Route::patch('doctor-schedules/{id}', [DoctorScheduleController::class, 'update']);
        Route::delete('doctor-schedules/{id}', [DoctorScheduleController::class, 'destroy']);
    });

    // Manajemen Sesi User
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('web');

    // Grup Khusus Pasien
    Route::middleware('role:patient')->prefix('patient')->group(function () {
        Route::get('/services', [PatientRegistrationController::class, 'getAvailableServices']);
        Route::post('/registrations', [PatientRegistrationController::class, 'store']);
        Route::get('/my-registrations', [PatientRegistrationController::class, 'myHistory']);
    });

    // Grup Khusus Perawat
    Route::middleware('role:nurse')->prefix('nurse')->group(function () {
        Route::get('/queues', [NurseQueueController::class, 'index']);
        Route::patch('/queues/{id}/verify', [NurseQueueController::class, 'verify']);
    });

    // Grup Khusus Dokter
    Route::middleware('role:doctor')->prefix('doctor')->group(function () {
        Route::get('/consultations', [DoctorConsultationController::class, 'index']);
        Route::patch('/consultations/{id}/status', [DoctorConsultationController::class, 'updateStatus']);
        Route::post('/consultations/{id}/inspection', [DoctorConsultationController::class, 'storeInspection']);
        Route::post('/consultations', [App\Http\Controllers\DoctorConsultationController::class, 'store']);
        Route::get('/patients/{id}/history', [App\Http\Controllers\DoctorConsultationController::class, 'getPatientHistory']);
        Route::get('/medicines', [App\Http\Controllers\DoctorConsultationController::class, 'getMedicines']);
    });

    // Rute Tagihan & Pembayaran
    Route::prefix('payments')->group(function () {
        Route::get('/{id}', [PaymentController::class, 'show']);

        // Pasien meminta URL pembayaran online (QRIS/VA)
        Route::post('/{id}/online', [PaymentController::class, 'payOnline']);

        // Perawat/Petugas Kasir memverifikasi pembayaran tunai
        Route::patch('/{id}/cash', [PaymentController::class, 'payCash'])
            ->middleware('role:nurse,admin');
    });
});
