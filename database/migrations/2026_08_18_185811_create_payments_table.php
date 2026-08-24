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
        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('registration_id')->unique()->constrained('registration', 'registration_id')->onDelete('cascade');
            $table->timestamp('payment_date')->useCurrent();
            $table->decimal('payment_total', 12, 2);
            $table->enum('payment_method', ['BPJS', 'QRIS', 'Debit Card', 'Kredit Card', 'Tunai']);
            $table->enum('payment_status', ['Unpaid', 'Paid', 'Canceled', 'Dp'])->default('Unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
