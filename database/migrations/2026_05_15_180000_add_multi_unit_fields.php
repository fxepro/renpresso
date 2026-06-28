<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('occupancy_mode', ['single', 'multi'])->default('single')->after('type');
            $table->unsignedSmallInteger('unit_capacity')->nullable()->after('occupancy_mode');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->string('unit_label', 64)->nullable()->after('tenant_id');
            $table->index(['property_id', 'unit_label']);
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'unit_label']);
            $table->dropColumn('unit_label');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['occupancy_mode', 'unit_capacity']);
        });
    }
};
