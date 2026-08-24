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
        Schema::create('room', function (Blueprint $table) {
            $table->id('room_id');
            $table->string('code_room', 10)->unique();
            $table->string('name_room', 255);
            $table->enum('type_room', ['Pemeriksaan', 'Rawat Inap', 'Operasi', 'IGD', 'Laboratorium']);
            $table->integer('capacity')->default(1);
            $table->integer('floor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room');
    }
};
