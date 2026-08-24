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
        Schema::create('doctor_poli', function (Blueprint $table) {
            $table->id('doctor_poli_id');
            $table->foreignId('doctor_id')->constrained('doctor', 'doctor_id')->onDelete('cascade');
            $table->foreignId('poli_id')->constrained('poli', 'poli_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_polis');
    }
};
