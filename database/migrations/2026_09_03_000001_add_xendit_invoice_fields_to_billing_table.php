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
        Schema::table('billing', function (Blueprint $table) {
            if (! Schema::hasColumn('billing', 'external_id')) {
                $table->string('external_id', 100)->nullable()->unique()->after('invoice_number');
            }

            if (! Schema::hasColumn('billing', 'xendit_invoice_id')) {
                $table->string('xendit_invoice_id', 100)->nullable()->after('xendit_id');
            }

            if (! Schema::hasColumn('billing', 'invoice_url')) {
                $table->text('invoice_url')->nullable()->after('xendit_payment_url');
            }

            if (! Schema::hasColumn('billing', 'payment_details')) {
                $table->text('payment_details')->nullable()->after('invoice_url');
            }

            if (! Schema::hasColumn('billing', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('total_amount');
            }

            if (! Schema::hasColumn('billing', 'appointment_id')) {
                $table->unsignedBigInteger('appointment_id')->nullable()->after('reservation_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('billing', 'external_id')) {
                $columnsToDrop[] = 'external_id';
            }
            if (Schema::hasColumn('billing', 'xendit_invoice_id')) {
                $columnsToDrop[] = 'xendit_invoice_id';
            }
            if (Schema::hasColumn('billing', 'invoice_url')) {
                $columnsToDrop[] = 'invoice_url';
            }
            if (Schema::hasColumn('billing', 'amount')) {
                $columnsToDrop[] = 'amount';
            }
            if (Schema::hasColumn('billing', 'appointment_id')) {
                $columnsToDrop[] = 'appointment_id';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
