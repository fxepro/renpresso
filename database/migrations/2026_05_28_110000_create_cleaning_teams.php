<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('city');
            $table->string('country_code', 2);
            $table->string('phone')->nullable();
            $table->json('services')->nullable();
            $table->boolean('is_listed')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['city', 'country_code', 'is_listed']);
        });

        Schema::create('cleaning_team_cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cleaning_team_id')->constrained('cleaning_teams')->cascadeOnDelete();
            $table->string('city');
            $table->string('country_code', 2);
            $table->string('region')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['cleaning_team_id', 'city', 'country_code']);
        });

        Schema::create('landlord_cleaning_team', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('cleaning_team_id')->constrained('cleaning_teams')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['landlord_id', 'cleaning_team_id']);
        });

        Schema::create('cleaning_team_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cleaning_team_id')->constrained('cleaning_teams')->cascadeOnDelete();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['cleaning_team_id', 'landlord_id']);
        });

        Schema::create('cleaning_staff_invites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignUuid('staff_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_staff_invites');
        Schema::dropIfExists('cleaning_team_reviews');
        Schema::dropIfExists('landlord_cleaning_team');
        Schema::dropIfExists('cleaning_team_cities');
        Schema::dropIfExists('cleaning_teams');
    }
};
