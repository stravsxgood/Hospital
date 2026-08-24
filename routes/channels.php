<?php

use App\Models\Billing;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Channel Privat Konsol Dokter (Panggilan antrean, check-in pasien baru)
 */
Broadcast::channel('doctor.{doctorId}', function (User $user, $doctorId) {
    // Dokter yang bersangkutan atau staf rumah sakit
    if ($user->doctor && (int) $user->doctor->doctor_id === (int) $doctorId) {
        return true;
    }

    // Perawat/Staf dan Koas diizinkan memantau status antrean dokter
    return $user->nurse !== null;
});

/**
 * Channel Privat Farmasi & Apotek (Resep masuk instan)
 */
Broadcast::channel('pharmacy', function (User $user) {
    // Tenaga staf/perawat, koas, dan dokter memiliki akses ke notifikasi resep farmasi
    return $user->nurse !== null || $user->doctor !== null;
});

/**
 * Channel Privat Tagihan Kasir & Pasien (Konfirmasi Pembayaran POS / QRIS)
 */
Broadcast::channel('billing.{billingId}', function (User $user, $billingId) {
    // Staf kasir / perawat selalu memiliki hak akses
    if ($user->nurse !== null) {
        return true;
    }

    // Pasien pemilik tagihan
    $billing = Billing::with('reservation')->find($billingId);
    if ($billing && $billing->reservation && $user->patient) {
        return (int) $billing->reservation->patient_id === (int) $user->patient->patient_id;
    }

    return false;
});
