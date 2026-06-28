<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maintenance_teams', function (Blueprint $table) {
            $table->json('services')->nullable()->after('description');
        });

        Schema::create('maintenance_team_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['maintenance_team_id', 'landlord_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_team_reviews');
        Schema::table('maintenance_teams', function (Blueprint $table) {
            $table->dropColumn('services');
        });
    }
};
