<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sent_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignUuid('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('lease_id')->nullable()->constrained('leases')->nullOnDelete();

            $table->string('to_email');
            $table->string('subject_sent');
            $table->date('trigger_date')->nullable();    // the due/event date this was fired for
            $table->string('trigger_key')->nullable();   // e.g. 'rent_reminder_5d:lease_uuid:2026-06-15'
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Prevent duplicate sends for the same trigger on the same day
            $table->unique(['trigger_key'], 'uq_trigger_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sent_logs');
    }
};
