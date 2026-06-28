<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_team_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('file_path', 512);
            $table->string('status', 32)->default('pending');
            $table->string('reference_number', 120)->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['maintenance_team_id', 'document_type']);
            $table->index(['maintenance_team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_team_documents');
    }
};
