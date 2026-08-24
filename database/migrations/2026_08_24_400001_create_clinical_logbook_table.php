<?php

declare(strict_types=1);

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
        Schema::create('clinical_logbook', function (Blueprint $table) {
            $table->id('clinical_logbook_id');
            $table->foreignId('nurse_id')
                ->constrained('nurse', 'nurse_id')
                ->onDelete('cascade');
            $table->foreignId('patient_id')
                ->constrained('patient', 'patient_id')
                ->onDelete('cascade');
            $table->foreignId('medical_record_id')
                ->nullable()
                ->constrained('medical_record', 'medical_record_id')
                ->nullOnDelete();
            $table->foreignId('doctor_id')
                ->constrained('doctor', 'doctor_id')
                ->onDelete('cascade');
            $table->enum('activity_type', ['anamnesis', 'physical_exam', 'procedure_assistance', 'case_discussion'])
                ->default('anamnesis');
            $table->string('case_title', 150);
            $table->text('clinical_findings');
            $table->text('procedure_performed')->nullable();
            $table->text('learning_reflection');
            $table->text('supervisor_feedback')->nullable();
            $table->integer('score')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'revision_needed'])
                ->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            // Indeks untuk pencarian & filter berdasarkan status dan mahasiswa koas
            $table->index(['nurse_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index('activity_type');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_logbook');
    }
};
