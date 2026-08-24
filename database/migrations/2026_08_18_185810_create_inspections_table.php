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
        Schema::create('inspection', function (Blueprint $table) {
            $table->id('inspection_id');
            $table->foreignId('registration_id')->unique()->constrained('registration', 'registration_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctor', 'doctor_id')->onDelete('cascade');
            $table->date('inspection_date')->useCurrent();
            $table->text('complain');
            $table->text('inspection_result');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
