<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('base_rent_minor_units')->nullable()->after('bedrooms');
            $table->unsignedBigInteger('rent_minor_units')->nullable()->after('base_rent_minor_units');
            $table->json('rent_charge_lines')->nullable()->after('rent_minor_units');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['base_rent_minor_units', 'rent_minor_units', 'rent_charge_lines']);
        });
    }
};
