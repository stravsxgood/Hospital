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
        Schema::create('room_poli', function (Blueprint $table) {
            $table->id('room_poli_id');
            $table->foreignId('room_id')->constrained('room', 'room_id')->onDelete('cascade');
            $table->foreignId('poli_id')->constrained('poli', 'poli_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_polis');
    }
};
