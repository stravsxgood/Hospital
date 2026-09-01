<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat tabel app_settings untuk menyimpan konfigurasi dinamis sistem SIMRS.
 *
 * Desain key-value dengan pengelompokan (group) memungkinkan Super Admin
 * mengelola pengaturan tampilan antrean publik (DisplayBoard), parameter
 * operasional, dan preferensi sistem lainnya tanpa perubahan kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
