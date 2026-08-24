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
        Schema::create('nurse', function (Blueprint $table) {
            $table->id('nurse_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('registration_number', 30)->unique()->nullable(); // STR atau NIM
            $table->enum('type', ['tetap', 'koas'])->default('tetap');
            $table->string('institute', 255)->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse');
    }
};
