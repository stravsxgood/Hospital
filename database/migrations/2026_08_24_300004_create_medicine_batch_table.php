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
        Schema::create('medicine_batch', function (Blueprint $table) {
            $table->id('medicine_batch_id');
            $table->foreignId('medicine_id')
                ->constrained('medicine', 'medicine_id')
                ->onDelete('cascade');
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('stock_quantity')->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0.00);
            $table->timestamps();

            // Indeks untuk query FEFO (First Expired First Out)
            $table->index(['medicine_id', 'expiry_date']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_batch');
    }
};
