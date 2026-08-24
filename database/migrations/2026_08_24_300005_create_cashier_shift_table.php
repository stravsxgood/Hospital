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
        Schema::create('cashier_shift', function (Blueprint $table) {
            $table->id('cashier_shift_id');
            $table->foreignId('nurse_id')
                ->constrained('nurse', 'nurse_id')
                ->onDelete('cascade');
            $table->string('shift_name', 50)->default('Pagi'); // 'Pagi', 'Siang', 'Malam'
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_cash', 12, 2)->default(0.00); // Kas awal / modal float kasir
            $table->decimal('closing_cash_actual', 12, 2)->nullable(); // Kas fisik yang dihitung saat tutup shift
            $table->decimal('total_cash_system', 12, 2)->default(0.00); // Total pembayaran tunai tercatat di sistem
            $table->decimal('total_qris_system', 12, 2)->default(0.00); // Total pembayaran non-tunai/QRIS tercatat di sistem
            $table->decimal('discrepancy', 12, 2)->nullable(); // Selisih kas (closing_cash_actual - (opening_cash + total_cash_system))
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['nurse_id', 'status']);
            $table->index('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shift');
    }
};
