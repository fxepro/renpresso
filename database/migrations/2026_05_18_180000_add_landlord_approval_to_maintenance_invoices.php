<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_invoices', function (Blueprint $table) {
            $table->timestamp('landlord_approved_at')->nullable()->after('cancelled_at');
            $table->foreignUuid('landlord_approved_by')->nullable()->after('landlord_approved_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landlord_approved_by');
            $table->dropColumn('landlord_approved_at');
        });
    }
};
