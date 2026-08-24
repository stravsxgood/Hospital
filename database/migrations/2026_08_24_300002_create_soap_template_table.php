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
        Schema::create('soap_template', function (Blueprint $table) {
            $table->id('soap_template_id');
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctor', 'doctor_id')
                ->nullOnDelete();
            $table->string('template_name', 100);
            $table->text('subjective_template')->nullable();
            $table->json('objective_template')->nullable();
            $table->text('assessment_template')->nullable();
            $table->text('plan_template')->nullable();
            $table->timestamps();

            $table->index('doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soap_template');
    }
};
