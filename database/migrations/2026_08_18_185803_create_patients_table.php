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
        Schema::create('patient', function (Blueprint $table) {
            $table->id('patient_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('resident_n', 16)->unique(); // NIK KTP
            $table->string('name', 255);
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->date('birthday_date');
            $table->text('address')->nullable();
            $table->string('number_phone', 15)->nullable();
            $table->text('disease')->nullable();
            $table->date('registration_date')->useCurrent();
            $table->enum('status', ['active', 'non active'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
