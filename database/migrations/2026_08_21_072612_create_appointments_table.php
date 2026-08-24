<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            // Menghubungkan ke primary key patient_id di tabel patient
            $table->foreignId('patient_id')
                ->constrained('patient', 'patient_id')
                ->cascadeOnDelete();

            $table->foreignId('doctor_schedule_id')
                ->constrained('doctor_schedule', 'doctor_schedule_id')
                ->cascadeOnDelete();

            $table->date('appointment_date');
            $table->string('queue_number', 20); // Contoh: "A-001" atau "001"
            $table->text('complaint')->nullable(); // Keluhan pasien
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            // 1. Composite Index untuk mempercepat query generate nomor antrean
            $table->index(['doctor_schedule_id', 'appointment_date'], 'idx_schedule_date');

            // 2. Unique Constraint mencegah pasien mendaftar ganda di sesi yang sama
            $table->unique(['patient_id', 'doctor_schedule_id', 'appointment_date'], 'unique_patient_booking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
