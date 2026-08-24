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
        Schema::create('doctor', function (Blueprint $table) {
            $table->id('doctor_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained('specialization', 'specialization_id')->onDelete('restrict');
            $table->string('name', 255);
            $table->string('sip_number', 30)->unique();
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->string('number_phone', 15)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->date('join_date');
            $table->enum('status', ['aktif', 'pensiun'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
