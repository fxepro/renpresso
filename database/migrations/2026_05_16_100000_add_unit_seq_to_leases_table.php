<?php

use App\Models\Lease;
use App\Models\Property;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->unsignedSmallInteger('unit_seq')->default(0)->after('tenant_id');
            $table->index(['property_id', 'unit_seq']);
        });

        foreach (Property::query()->withTrashed()->cursor() as $property) {
            $leases = Lease::withTrashed()
                ->where('property_id', $property->id)
                ->orderBy('created_at')
                ->get();
            if ($leases->isEmpty()) {
                continue;
            }
            if ($property->occupancy_mode !== 'multi') {
                foreach ($leases as $lease) {
                    $lease->unit_seq = 0;
                    $lease->saveQuietly();
                }
                continue;
            }
            $n = 0;
            foreach ($leases as $lease) {
                $n++;
                $lease->unit_seq = $n;
                $lease->saveQuietly();
            }
        }
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'unit_seq']);
            $table->dropColumn('unit_seq');
        });
    }
};
