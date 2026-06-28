<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->json('unit_slots_meta')->nullable()->after('unit_capacity');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_multi_unit_capacity')->nullable()->after('billing_pm_last4');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('unit_slots_meta');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_multi_unit_capacity');
        });
    }
};
