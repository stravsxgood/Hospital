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
        Schema::create('prescription', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('medical_record_id')
                ->constrained('medical_record', 'medical_record_id')
                ->onDelete('cascade');
            $table->string('prescription_number', 50)->unique();
            $table->enum('status', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index status resep untuk antrean farmasi
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription');
    }
};
