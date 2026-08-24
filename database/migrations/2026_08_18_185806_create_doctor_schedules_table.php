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
        Schema::create('doctor_schedule', function (Blueprint $table) {
            $table->id('doctor_schedule_id');
            $table->foreignId('doctor_id')->constrained('doctor', 'doctor_id')->onDelete('cascade');
            $table->foreignId('poli_id')->constrained('poli', 'poli_id')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room', 'room_id')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('quota_day')->default(20);
            $table->enum('status', ['Aktif', 'Libur'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
