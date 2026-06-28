<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('landlord_payout_accounts', function (Blueprint $table) {
            $table->string('purpose', 24)->default('collection')->after('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('landlord_payout_accounts', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
