<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('residence_country');
            $table->string('portfolio_size')->nullable();
            $table->string('property_countries')->nullable();
            $table->string('pain_point')->nullable();
            $table->foreignUuid('waitlist_email_id')->nullable()->constrained('waitlist_emails')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('waitlist_emails', function (Blueprint $table) {
            $table->foreignUuid('converted_user_id')->nullable()->after('ref')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_emails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_user_id');
        });

        Schema::dropIfExists('landlord_profiles');
    }
};
