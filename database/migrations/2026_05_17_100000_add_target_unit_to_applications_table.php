<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('target_unit_label', 64)->nullable()->after('property_id');
            $table->index(['property_id', 'target_unit_label']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'target_unit_label']);
            $table->dropColumn('target_unit_label');
        });
    }
};
