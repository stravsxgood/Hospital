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
        Schema::create('prescription_item', function (Blueprint $table) {
            $table->id('prescription_item_id');
            $table->foreignId('prescription_id')
                ->constrained('prescription', 'prescription_id')
                ->onDelete('cascade');
            $table->foreignId('medicine_id')
                ->constrained('medicine', 'medicine_id')
                ->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->string('dosage', 100); // contoh: "3x1 Sehari", "2x1 Tablet"
            $table->string('instructions', 255); // contoh: "Sesudah makan", "Sebelum makan"
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            // Index relasi resep dan obat
            $table->index(['prescription_id', 'medicine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_item');
    }
};
