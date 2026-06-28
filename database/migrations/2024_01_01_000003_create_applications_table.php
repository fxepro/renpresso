<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('move_in_date')->nullable();
            $table->bigInteger('monthly_income_minor_units')->nullable();
            $table->string('income_currency', 3)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending','reviewing','approved','rejected'])->default('pending');
            $table->text('landlord_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['property_id', 'status']);
        });

        Schema::create('background_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit','criminal','eviction','right_to_rent','employment','references','document_upload']);
            $table->enum('method', ['checkr','experian','transunion','document_upload'])->default('document_upload');
            $table->enum('status', ['requested','pending','passed','failed','manual_review'])->default('requested');
            $table->string('provider_ref')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_checks');
        Schema::dropIfExists('applications');
    }
};
