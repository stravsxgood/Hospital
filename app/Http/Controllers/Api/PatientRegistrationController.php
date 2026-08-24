<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Doctor;
use App\Models\Poli;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientRegistrationController extends Controller
{
    /**
     * Menampilkan daftar Poli yang aktif beserta dokter dan jadwalnya
     */
    public function getAvailableServices(): JsonResponse
    {
        $services = Poli::where('status', 'Aktif')
            ->with([
                'doctors.specialization',
                'doctors.schedules' => function ($query) {
                    $query->where('status', 'Aktif')->orderBy('day');
                }
            ])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $services,
        ]);
    }

    /**
     * Mendaftar antrean baru (Booking)
     */
    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        $patient = $request->user()->patient;

        $registration = Registration::create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $request->doctor_id,
            'poli_id' => $request->poli_id,
            'registration_date' => $request->registration_date,
            'status' => 'Menunggu',
            'queue_number' => null, // Diterbitkan oleh perawat saat verifikasi
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran antrean berhasil. Silakan tunggu verifikasi perawat saat kedatangan.',
            'data' => $registration->load(['doctor.specialization', 'poli']),
        ], 201);
    }

    /**
     * Riwayat pendaftaran antrean milik pasien yang login
     */
    public function myHistory(Request $request): JsonResponse
    {
        $patientId = $request->user()->patient->patient_id;

        $history = Registration::byPatient($patientId)
            ->with(['doctor.specialization', 'poli', 'inspection', 'payment'])
            ->orderBy('registration_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }
}
