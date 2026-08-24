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
        Schema::create('patient_allergy', function (Blueprint $table) {
            $table->id('patient_allergy_id');
            $table->foreignId('patient_id')
                ->constrained('patient', 'patient_id')
                ->onDelete('cascade');
            $table->string('allergen_type', 50)->default('medicine'); // 'medicine', 'food', 'environment'
            $table->string('allergen_name', 150);
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('moderate');
            $table->string('reaction', 255)->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'allergen_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_allergy');
    }
};
