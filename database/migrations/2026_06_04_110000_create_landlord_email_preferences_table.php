<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_email_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('email_template_id')->constrained('email_templates')->cascadeOnDelete();

            $table->boolean('is_enabled')->default(true);
            $table->string('subject_override')->nullable();
            $table->longText('body_html_override')->nullable();

            $table->timestamps();

            $table->unique(['landlord_id', 'email_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_email_preferences');
    }
};
