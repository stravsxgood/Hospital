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
        Schema::create('medicine', function (Blueprint $table) {
            $table->id('medicine_id');
            $table->string('code_medicine', 50)->unique();
            $table->string('name_medicine', 255);
            $table->string('type', 100); // tablet, sirup, kapsul, salep, tetes, inhaler, dll
            $table->integer('stock')->default(0);
            $table->string('unit', 50); // strip, botol, tablet, kapsul, tube, vial, pcs
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            // Index pencarian obat berdasarkan nama dan kode
            $table->index(['name_medicine', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine');
    }
};
