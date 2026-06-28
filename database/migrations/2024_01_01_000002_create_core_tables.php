<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Properties ──────────────────────────────────────────────
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');                          // e.g. "Bandra West Flat"
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code', 2);              // ISO 3166-1 alpha-2
            $table->string('currency_code', 3);             // ISO 4217 — locked to country
            $table->string('processor_slug', 50);           // stripe | razorpay | flutterwave etc.
            $table->enum('type', ['apartment', 'house', 'commercial', 'other'])->default('apartment');
            $table->integer('bedrooms')->nullable();
            $table->enum('status', ['active', 'vacant', 'archived'])->default('vacant');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['landlord_id', 'status']);
            $table->index('country_code');
        });

        // ── Leases ──────────────────────────────────────────────────
        Schema::create('leases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('rent_minor_units');          // always minor units (paise, pence, cents)
            $table->string('currency_code', 3);
            $table->unsignedTinyInteger('due_day');          // 1–28
            $table->unsignedTinyInteger('grace_period_days')->default(5);
            $table->enum('frequency', ['monthly', 'fortnightly', 'weekly'])->default('monthly');
            $table->bigInteger('deposit_minor_units')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
            $table->index(['tenant_id', 'status']);
        });

        // ── Payment mandates ─────────────────────────────────────────
        Schema::create('payment_mandates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->string('processor_slug', 50);
            $table->string('processor_mandate_id');          // processor's own mandate/subscription ID
            $table->enum('status', ['pending', 'active', 'cancelled', 'failed'])->default('pending');
            $table->string('payment_method_type')->nullable(); // upi | sepa_debit | bacs_debit | ach etc.
            $table->timestamp('authorised_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['lease_id', 'status']);
            $table->unique(['lease_id', 'processor_mandate_id']);
        });

        // ── Payments ─────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('mandate_id')->constrained('payment_mandates');
            $table->string('processor_ref')->unique();       // processor's own transaction ID
            $table->bigInteger('amount_minor_units');
            $table->string('currency_code', 3);
            $table->bigInteger('fx_rate_snapshot');          // rate × 1,000,000 — NEVER recalculated
            $table->string('home_currency_code', 3);         // landlord's home currency at time of payment
            $table->bigInteger('home_amount_minor_units');   // amount converted at snapshot rate
            $table->enum('status', ['pending', 'success', 'failed', 'refunded', 'disputed'])->default('pending');
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->timestamp('due_date');
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lease_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('collected_at');
        });

        // ── Payment events (full audit trail) ────────────────────────
        Schema::create('payment_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 100);               // payment.success | payment.failed | webhook.received etc.
            $table->string('idempotency_key')->unique();     // prevents duplicate webhook processing
            $table->json('processor_payload');               // full raw webhook/API response
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['payment_id', 'event_type']);
        });

        // ── Repatriation log ─────────────────────────────────────────
        Schema::create('repatriation_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_minor_units');
            $table->string('currency_code', 3);              // source currency (EUR, INR etc.)
            $table->bigInteger('fx_rate_snapshot');           // rate used at repatriation
            $table->string('home_currency_code', 3);
            $table->bigInteger('home_amount_minor_units');
            $table->date('repatriated_on');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['landlord_id', 'repatriated_on']);
        });

        // ── Maintenance requests ─────────────────────────────────────
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('raised_by')->constrained('users');
            $table->enum('category', ['plumbing', 'electrical', 'heating', 'structural', 'appliance', 'other']);
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['submitted', 'acknowledged', 'in_progress', 'resolved'])->default('submitted');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lease_id', 'status']);
        });

        // ── Messages ─────────────────────────────────────────────────
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sender_id')->constrained('users');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lease_id', 'created_at']);
        });

        // ── Documents ────────────────────────────────────────────────
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('documentable');              // property | lease | maintenance_request
            $table->foreignUuid('uploaded_by')->constrained('users');
            $table->enum('type', ['lease', 'inspection', 'insurance', 'compliance', 'receipt', 'other']);
            $table->string('disk', 50)->default('s3');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('expires_at')->nullable();     // for signed URL expiry
            $table->timestamps();
            $table->softDeletes();

            $table->index('documentable_id');
        });

        // ── Waitlist ─────────────────────────────────────────────────
        Schema::create('waitlist_emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('home_country')->nullable();
            $table->string('property_countries')->nullable(); // comma-separated
            $table->string('portfolio_size')->nullable();
            $table->string('pain_point')->nullable();
            $table->string('ref')->unique();                  // RMX-XXXXXX reference
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_emails');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('repatriation_logs');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_mandates');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('properties');
    }
};
