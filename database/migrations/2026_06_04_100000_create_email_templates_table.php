<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identity
            $table->string('slug')->unique();          // e.g. 'rent_reminder_5d'
            $table->string('name');                    // human label
            $table->string('trigger_event');           // 'rent_due_in_days' | 'rent_overdue_days' | 'payment_success' | 'payment_failed' | 'late_fee_applied' | 'lease_expiry_days'
            $table->unsignedSmallInteger('trigger_days')->nullable(); // null = event-based
            $table->enum('trigger_direction', ['before', 'after', 'on'])->nullable();

            // Content
            $table->string('subject');
            $table->longText('body_html');
            $table->json('available_variables')->nullable(); // doc array for UI hints

            // Config
            $table->boolean('is_published')->default(false);
            $table->boolean('landlord_can_edit')->default(true);  // landlords may override
            $table->boolean('landlord_can_disable')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
