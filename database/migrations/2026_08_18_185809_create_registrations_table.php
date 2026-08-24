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
        Schema::create('registration', function (Blueprint $table) {
            $table->id('registration_id');
            $table->string('queue_number', 10)->nullable();
            $table->foreignId('patient_id')->constrained('patient', 'patient_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctor', 'doctor_id')->onDelete('cascade');
            $table->foreignId('poli_id')->constrained('poli', 'poli_id')->onDelete('cascade');
            $table->foreignId('verified_by_nurse_id')->nullable()->constrained('nurse', 'nurse_id')->onDelete('set null');
            $table->date('registration_date')->useCurrent();
            $table->enum('status', ['Menunggu', 'Terverifikasi', 'Dipanggil', 'Diperiksa', 'Selesai', 'Batal'])->default('Menunggu');
            $table->timestamps();

            // Index pencarian harian
            $table->index(['registration_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
