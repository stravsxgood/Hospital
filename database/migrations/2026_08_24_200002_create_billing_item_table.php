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
        Schema::create('billing_item', function (Blueprint $table) {
            $table->id('billing_item_id');

            // Foreign Key ke tabel billing
            $table->foreignId('billing_id')
                ->constrained('billing', 'billing_id')
                ->cascadeOnDelete();

            // Tipe item: consultation_fee, medicine, procedure
            $table->string('item_type', 50);

            // Nama item rincian
            $table->string('item_name', 255);

            // Kuantitas
            $table->integer('quantity')->default(1);

            // Harga satuan & Subtotal
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);

            $table->timestamps();

            // Indeks rincian tagihan
            $table->index(['billing_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_item');
    }
};
