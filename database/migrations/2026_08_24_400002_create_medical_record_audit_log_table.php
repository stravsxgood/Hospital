<?php

declare(strict_types=1);

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
        Schema::create('medical_record_audit_log', function (Blueprint $table) {
            $table->id('audit_log_id');
            $table->foreignId('medical_record_id')
                ->constrained('medical_record', 'medical_record_id')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->onDelete('cascade');
            $table->enum('action', ['view', 'create', 'update', 'export_pdf', 'print'])
                ->default('view');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('payload_diff')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indeks penelusuran audit kepatuhan UU PDP & hukum medis
            $table->index(['medical_record_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_record_audit_log');
    }
};
