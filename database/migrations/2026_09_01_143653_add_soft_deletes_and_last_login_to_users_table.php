<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom SoftDeletes (deleted_at) dan last_login_at pada tabel users.
 *
 * - SoftDeletes: Mencegah penghapusan fisik user yang memiliki riwayat EMR/SOAP,
 *   menjaga integritas referensial foreign key.
 * - last_login_at: Melacak aktivitas login terakhir untuk keperluan audit
 *   dan pembersihan akun inaktif oleh Super Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('last_login_at');
        });
    }
};
