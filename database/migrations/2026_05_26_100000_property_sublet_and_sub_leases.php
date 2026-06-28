<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('sublet_allowed')->default(false)->after('listing_visibility');
            $table->boolean('sublet_bg_check_required')->default(true)->after('sublet_allowed');
            $table->boolean('sublet_landlord_approval_required')->default(true)->after('sublet_bg_check_required');
        });

        Schema::create('sub_leases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_lease_id')->constrained('leases')->cascadeOnDelete();
            $table->foreignUuid('subletter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('rent_minor_units');
            $table->string('currency_code', 3);
            $table->unsignedTinyInteger('due_day');
            $table->unsignedTinyInteger('grace_period_days')->default(5);
            $table->string('frequency', 20)->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 40)->default('draft');
            $table->timestamp('landlord_approved_at')->nullable();
            $table->string('landlord_rejection_reason')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['parent_lease_id', 'status']);
            $table->index('subletter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_leases');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'sublet_allowed',
                'sublet_bg_check_required',
                'sublet_landlord_approval_required',
            ]);
        });
    }
};
