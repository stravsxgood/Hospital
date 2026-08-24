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
        Schema::create('billing', function (Blueprint $table) {
            $table->id('billing_id');

            // Foreign Key ke appointment / reservation
            if (Schema::hasTable('appointments')) {
                $table->foreignId('reservation_id')
                    ->constrained('appointments', 'appointment_id')
                    ->onDelete('cascade');
            } elseif (Schema::hasTable('reservation')) {
                $table->foreignId('reservation_id')
                    ->constrained('reservation', 'reservation_id')
                    ->onDelete('cascade');
            } else {
                $table->unsignedBigInteger('reservation_id');
            }

            // Foreign Key ke tabel patient
            $table->foreignId('patient_id')
                ->constrained('patient', 'patient_id')
                ->onDelete('cascade');

            // Petugas perawat/kasir tetap yang memproses pembayaran
            $table->foreignId('processed_by_nurse_id')
                ->nullable()
                ->constrained('nurse', 'nurse_id')
                ->nullOnDelete();

            // Nomor Invoice unik: e.g. INV-20260824-0001
            $table->string('invoice_number', 50)->unique();

            // Rincian total tagihan
            $table->decimal('total_amount', 12, 2)->default(0.00);

            // Status pembayaran: unpaid, pending, paid, expired, cancelled
            $table->enum('status', ['unpaid', 'pending', 'paid', 'expired', 'cancelled'])->default('unpaid');

            // Metode pembayaran: cash, xendit_invoice, xendit_qris
            $table->string('payment_method', 50)->nullable();

            // Xendit External ID & Payment Link
            $table->string('xendit_id', 100)->nullable()->index();
            $table->text('xendit_payment_url')->nullable();

            // Waktu pelunasan
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Indeks pencarian billing
            $table->index(['patient_id', 'status']);
            $table->index(['reservation_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing');
    }
};
