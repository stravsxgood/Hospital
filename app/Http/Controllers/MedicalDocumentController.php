<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Billing;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class MedicalDocumentController extends Controller
{
    /**
     * Cetak Dokumen Resume Medis Pasien (Bisa diakses Staf Tetap & Koas).
     */
    public function printResume(int $reservationId, Request $request): Response
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'doctorSchedule.room',
            'medicalRecord.prescription.items.medicine',
        ])->findOrFail($reservationId);

        $patient = $appointment->patient;
        $medicalRecord = $appointment->medicalRecord;
        $nurse = $request->user()?->nurse;

        if ($medicalRecord) {
            \App\Services\AuditLogService::logAccess(
                medicalRecordId: (int) $medicalRecord->medical_record_id,
                action: 'export_pdf',
                payloadDiff: ['document' => 'Resume Medis', 'stream' => $request->query('stream') === '1']
            );
        }

        $pdf = Pdf::loadView('pdf.medical_resume', [
            'appointment'   => $appointment,
            'patient'       => $patient,
            'medicalRecord' => $medicalRecord,
            'nurse'         => $nurse,
        ])->setPaper('a4', 'portrait');

        $filename = 'Resume_Medis_' . str_replace(' ', '_', $patient->name) . '_' . date('Ymd') . '.pdf';

        if ($request->query('stream') === '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Cetak Surat Keterangan Sakit / Istirahat Dokter (Bisa diakses Staf Tetap & Koas).
     */
    public function printSickLetter(int $reservationId, Request $request): Response
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'medicalRecord',
        ])->findOrFail($reservationId);

        $days = (int) $request->query('days', 3);
        $patient = $appointment->patient;
        $medicalRecord = $appointment->medicalRecord;
        $nurse = $request->user()?->nurse;

        $pdf = Pdf::loadView('pdf.sick_letter', [
            'appointment'   => $appointment,
            'patient'       => $patient,
            'medicalRecord' => $medicalRecord,
            'nurse'         => $nurse,
            'days'          => $days,
        ])->setPaper('a4', 'portrait');

        $filename = 'Surat_Keterangan_Sakit_' . str_replace(' ', '_', $patient->name) . '_' . date('Ymd') . '.pdf';

        if ($request->query('stream') === '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Cetak Surat Rujukan Medis Eksternal (Bisa diakses Staf Tetap & Koas).
     */
    public function printReferral(int $reservationId, Request $request): Response
    {
        $appointment = Appointment::with([
            'patient.user',
            'doctorSchedule.doctor.specialization',
            'doctorSchedule.poli',
            'medicalRecord.prescription.items.medicine',
        ])->findOrFail($reservationId);

        $patient = $appointment->patient;
        $medicalRecord = $appointment->medicalRecord;
        $nurse = $request->user()?->nurse;

        $pdf = Pdf::loadView('pdf.referral_letter', [
            'appointment'   => $appointment,
            'patient'       => $patient,
            'medicalRecord' => $medicalRecord,
            'nurse'         => $nurse,
        ])->setPaper('a4', 'portrait');

        $filename = 'Surat_Rujukan_' . str_replace(' ', '_', $patient->name) . '_' . date('Ymd') . '.pdf';

        if ($request->query('stream') === '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Cetak Kuitansi Pembayaran Kasir (Khusus Staf / Perawat Tetap).
     */
    public function printReceipt(int $id, Request $request): Response
    {
        // Enforce RBAC Gate
        Gate::authorize('access-pekerja-only');

        $billing = Billing::with([
            'patient.user',
            'reservation.doctorSchedule.doctor.specialization',
            'reservation.doctorSchedule.poli',
            'items',
            'processedByNurse',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.payment_receipt', [
            'billing' => $billing,
        ])->setPaper('a4', 'portrait');

        $filename = 'Kuitansi_Pembayaran_' . $billing->invoice_number . '.pdf';

        if ($request->query('stream') === '1') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
