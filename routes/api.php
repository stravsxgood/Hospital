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

// Public Endpoints
Route::post('/register', [AuthController::class, 'registerPatient']);

// Tambahkan middleware 'web' agar sesi browser aktif saat login via API
Route::post('/login', [AuthController::class, 'login'])->middleware('web');

// 1. Endpoint Webhook Publik (Wajib di luar auth middleware)
Route::post('/xendit/webhook', [PaymentController::class, 'handleWebhook'])
    ->middleware(VerifyXenditWebhook::class);
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handle']);
Route::post('/webhooks/xendit/qr', [XenditWebhookController::class, 'handleQrCallback']);

// 2. Endpoint untuk jadwal dokter
Route::apiResource('doctor-schedules', DoctorScheduleController::class);

Route::middleware('auth:sanctum')->group(function () {

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
