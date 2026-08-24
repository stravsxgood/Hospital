<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_record', function (Blueprint $table) {
            $table->id('medical_record_id');

            // Foreign key ke tabel appointment / reservation
            if (Schema::hasTable('appointments')) {
                $table->foreignId('reservation_id')
                    ->nullable()
                    ->constrained('appointments', 'appointment_id')
                    ->onDelete('set null');
            } elseif (Schema::hasTable('reservation')) {
                $table->foreignId('reservation_id')
                    ->nullable()
                    ->constrained('reservation', 'reservation_id')
                    ->onDelete('set null');
            } else {
                $table->unsignedBigInteger('reservation_id')->nullable();
            }

            // Foreign keys ke pasien dan dokter
            $table->foreignId('patient_id')
                ->constrained('patient', 'patient_id')
                ->onDelete('cascade');

            $table->foreignId('doctor_id')
                ->constrained('doctor', 'doctor_id')
                ->onDelete('cascade');

            // SOAP Data Columns
            $table->text('subjective'); // Keluhan subjektif & riwayat penyakit sekarang
            $table->json('objective'); // Vital signs (tensi, nadi, suhu, rr, tb, bb, bmi) dalam format JSON
            $table->text('assessment'); // Diagnosis medis kerja / ICD-10
            $table->text('plan'); // Rencana terapi & instruksi edukasi
            $table->text('physical_check')->nullable(); // Catatan tambahan pemeriksaan fisik

            $table->timestamps();

            // Indeks untuk pencarian rekam medis pasien dan dokter
            $table->index(['patient_id', 'created_at']);
            $table->index(['doctor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_record');
    }
};
