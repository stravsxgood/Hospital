<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\PatientCalledEvent;
use App\Events\PatientConfirmedEvent;
use App\Events\PaymentSettledEvent;
use App\Events\PrescriptionCreatedEvent;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Medicine;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Room;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

function createBroadcastingTestEnvironment(): array
{
    $spec = Specialization::create([
        'code_specialization' => 'SPEC-INT',
        'name_specialization' => 'Spesialis Penyakit Dalam',
    ]);
    $poli = Poli::create([
        'kode_poli' => 'POLI-INT',
        'name_poli' => 'Poli Penyakit Dalam',
        'location' => 'Lantai 1 Sayap Barat',
        'status' => 'Aktif',
    ]);
    $room = Room::create([
        'code_room' => 'RM-101',
        'name_room' => 'Ruang 101',
        'type_room' => 'Pemeriksaan',
        'capacity' => 5,
        'floor' => 1,
    ]);

    $doctorUser = User::factory()->create(['role' => 'doctor', 'name' => 'dr. Tirta Mandira']);
    $doctor = Doctor::create([
        'user_id' => $doctorUser->id,
        'specialization_id' => $spec->specialization_id,
        'name' => 'dr. Tirta Mandira',
        'sip_number' => 'SIP.12345.2026',
        'gender' => 'Laki-laki',
        'join_date' => '2020-01-01',
        'status' => 'aktif',
    ]);

    $nurseUser = User::factory()->create(['role' => 'nurse', 'name' => 'Ns. Suster Sarah']);
    $nurse = Nurse::create([
        'user_id' => $nurseUser->id,
        'name' => 'Ns. Suster Sarah',
        'registration_number' => 'STR-NURSE-001',
        'type' => 'tetap',
        'gender' => 'Perempuan',
    ]);

    $patientUser = User::factory()->create(['role' => 'patient', 'name' => 'Bapak Budi Santoso']);
    $patient = Patient::create([
        'user_id' => $patientUser->id,
        'name' => 'Bapak Budi Santoso',
        'resident_n' => '3201234567890001',
        'gender' => 'Laki-laki',
        'birthday_date' => '1985-05-15',
        'number_phone' => '08123456789',
        'registration_date' => Carbon::today()->toDateString(),
    ]);

    $schedule = DoctorSchedule::create([
        'doctor_id' => $doctor->doctor_id,
        'poli_id' => $poli->poli_id,
        'room_id' => $room->room_id,
        'day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '16:00',
        'quota_day' => 30,
        'status' => 'Aktif',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_schedule_id' => $schedule->doctor_schedule_id,
        'appointment_date' => Carbon::today()->toDateString(),
        'queue_number' => 'A-001',
        'status' => 'pending',
    ]);

    return compact('doctorUser', 'doctor', 'nurseUser', 'nurse', 'patientUser', 'patient', 'schedule', 'appointment', 'poli', 'room');
}

test('calling a patient broadcasts PatientCalledEvent over queue-display and doctor channels', function () {
    Event::fake([PatientCalledEvent::class]);

    $env = createBroadcastingTestEnvironment();

    $response = $this->actingAs($env['doctorUser'])
        ->patch("/doctor/queue/{$env['appointment']->appointment_id}/call");

    $response->assertRedirect();

    Event::assertDispatched(PatientCalledEvent::class, function (PatientCalledEvent $event) use ($env) {
        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->toArray();

        return in_array('queue-display', $channels)
            && in_array('private-doctor.' . $env['doctor']->doctor_id, $channels)
            && $event->queueNumber === 'A-001'
            && $event->patientName === 'Bapak Budi Santoso';
    });
});

test('confirming patient arrival broadcasts PatientConfirmedEvent to doctor private channel', function () {
    Event::fake([PatientConfirmedEvent::class]);

    $env = createBroadcastingTestEnvironment();

    $response = $this->actingAs($env['nurseUser'])
        ->postJson("/staff/reservations/{$env['appointment']->appointment_id}/confirm-arrival");

    $response->assertOk();

    Event::assertDispatched(PatientConfirmedEvent::class, function (PatientConfirmedEvent $event) use ($env) {
        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->toArray();

        return in_array('private-doctor.' . $env['doctor']->doctor_id, $channels)
            && $event->queueNumber === 'A-001'
            && $event->status === 'confirmed';
    });
});

test('creating consultation with prescription broadcasts PrescriptionCreatedEvent to pharmacy channel', function () {
    Event::fake([PrescriptionCreatedEvent::class]);

    $env = createBroadcastingTestEnvironment();

    $med = Medicine::create([
        'code_medicine' => 'OBT-001',
        'name_medicine' => 'Amoxicillin 500mg',
        'type' => 'Tablet',
        'price' => 5000,
        'stock' => 100,
        'unit' => 'Tablet',
    ]);

    $response = $this->actingAs($env['doctorUser'])
        ->postJson('/doctor/consultations', [
            'patient_id' => $env['patient']->patient_id,
            'reservation_id' => $env['appointment']->appointment_id,
            'subjective' => 'Demam dan radang tenggorokan',
            'objective' => ['temperature' => 38.5, 'pulse' => 88],
            'assessment' => 'Faringitis Akut',
            'plan' => 'Antibiotik dan istirahat',
            'prescription_items' => [
                [
                    'medicine_id' => $med->medicine_id,
                    'quantity' => 10,
                    'dosage' => '3x1 Tablet',
                    'instructions' => 'Sesudah makan',
                ],
            ],
        ]);

    $response->assertCreated();

    Event::assertDispatched(PrescriptionCreatedEvent::class, function (PrescriptionCreatedEvent $event) {
        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->toArray();

        return in_array('private-pharmacy', $channels)
            && $event->totalItems === 1
            && $event->patientName === 'Bapak Budi Santoso';
    });
});

test('settling cashier payment broadcasts PaymentSettledEvent to billing private channel', function () {
    Event::fake([PaymentSettledEvent::class]);

    $env = createBroadcastingTestEnvironment();

    $billing = Billing::create([
        'reservation_id' => $env['appointment']->appointment_id,
        'patient_id' => $env['patient']->patient_id,
        'invoice_number' => 'INV-TEST-001',
        'status' => 'pending',
        'payment_method' => 'cash',
        'total_amount' => 175000.00,
    ]);

    $response = $this->actingAs($env['nurseUser'])
        ->postJson("/staff/billing/{$billing->billing_id}/pay-cash", [
            'cash_received' => 200000,
        ]);

    $response->assertOk();

    Event::assertDispatched(PaymentSettledEvent::class, function (PaymentSettledEvent $event) use ($billing) {
        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->toArray();

        return in_array('private-billing.' . $billing->billing_id, $channels)
            && $event->status === 'paid'
            && $event->billingId === $billing->billing_id;
    });
});
