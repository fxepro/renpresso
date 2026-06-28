<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('rental_mode', 20)->default('long_term')->after('status');
            $table->string('listing_visibility', 20)->default('private')->after('rental_mode');

            $table->index(['listing_visibility', 'rental_mode', 'country_code'], 'properties_public_listing_idx');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_public_listing_idx');
            $table->dropColumn(['rental_mode', 'listing_visibility']);
        });
    }
};
