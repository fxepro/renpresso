<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('landlord_maintenance_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('staff_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['landlord_id', 'staff_id']);
        });

        Schema::create('maintenance_staff_invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignUuid('staff_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['landlord_id', 'email']);
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignUuid('assignee_id')->nullable()->after('raised_by')->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assignee_id');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignee_id');
            $table->dropColumn('assigned_at');
        });
        Schema::dropIfExists('maintenance_staff_invites');
        Schema::dropIfExists('landlord_maintenance_staff');
    }
};
