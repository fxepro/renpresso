<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BPS = 500;

    public function up(): void
    {
        if (Schema::hasColumn('platform_settings', 'default_maintenance_fee_minor_per_closure')) {
            Schema::table('platform_settings', function (Blueprint $table) {
                $table->unsignedSmallInteger('default_maintenance_commission_bps')->default(self::BPS);
            });
            DB::table('platform_settings')->update(['default_maintenance_commission_bps' => self::BPS]);
            Schema::table('platform_settings', function (Blueprint $table) {
                $table->dropColumn('default_maintenance_fee_minor_per_closure');
            });
        } elseif (Schema::hasColumn('platform_settings', 'default_maintenance_commission_bps')) {
            DB::table('platform_settings')->update(['default_maintenance_commission_bps' => self::BPS]);
        }

        if (Schema::hasColumn('country_markets', 'maintenance_fee_minor_per_closure')) {
            Schema::table('country_markets', function (Blueprint $table) {
                $table->unsignedSmallInteger('maintenance_commission_bps')->default(self::BPS);
            });
            DB::table('country_markets')->update(['maintenance_commission_bps' => self::BPS]);
            Schema::table('country_markets', function (Blueprint $table) {
                $table->dropColumn('maintenance_fee_minor_per_closure');
            });
        } elseif (Schema::hasColumn('country_markets', 'maintenance_commission_bps')) {
            DB::table('country_markets')->update(['maintenance_commission_bps' => self::BPS]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('platform_settings', 'default_maintenance_commission_bps')) {
            DB::table('platform_settings')->update(['default_maintenance_commission_bps' => 300]);
        }
        if (Schema::hasColumn('country_markets', 'maintenance_commission_bps')) {
            DB::table('country_markets')->update(['maintenance_commission_bps' => 300]);
        }
    }
};
