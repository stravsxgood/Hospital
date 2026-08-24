<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update PostgreSQL check constraint for appointments status to include 'in_progress'
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check;");
            DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'confirmed', 'in_progress', 'completed', 'cancelled'));");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check;");
            DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled'));");
        }
    }
};
