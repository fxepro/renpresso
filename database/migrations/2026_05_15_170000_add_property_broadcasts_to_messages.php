<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignUuid('property_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->uuid('lease_id')->nullable()->change();
            $table->index(['property_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropIndex(['property_id', 'created_at']);
            $table->dropColumn('property_id');
        });
    }
};
