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
        Schema::create('icd10_diagnosis', function (Blueprint $table) {
            $table->id('icd10_diagnosis_id');
            $table->string('code', 20)->unique();
            $table->string('name_id', 255);
            $table->string('name_en', 255)->nullable();
            $table->boolean('is_common')->default(false);
            $table->timestamps();

            // Indeks pencarian kode diagnosis
            $table->index('code');
            $table->index('is_common');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icd10_diagnosis');
    }
};
