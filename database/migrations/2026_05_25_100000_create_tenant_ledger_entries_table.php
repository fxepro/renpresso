<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->bigInteger('ledger_starting_balance_minor_units')->default(0)->after('deposit_minor_units');
        });

        Schema::create('tenant_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('description');
            $table->string('paid_by')->nullable();
            $table->bigInteger('charge_minor_units')->default(0);
            $table->bigInteger('payment_minor_units')->default(0);
            $table->string('category', 40)->nullable();
            $table->foreignUuid('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['lease_id', 'entry_date', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_ledger_entries');

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('ledger_starting_balance_minor_units');
        });
    }
};
