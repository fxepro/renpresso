<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_team_cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->string('city', 120);
            $table->string('country_code', 2);
            $table->string('region', 120)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['maintenance_team_id', 'country_code']);
            $table->unique(['maintenance_team_id', 'city', 'country_code']);
        });

        Schema::create('maintenance_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->foreignUuid('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('maintenance_request_id')->nullable()->constrained('maintenance_requests')->nullOnDelete();
            $table->string('invoice_number', 32);
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 3);
            $table->string('status', 20)->default('draft');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['maintenance_team_id', 'invoice_number']);
            $table->index(['maintenance_team_id', 'status']);
        });

        Schema::create('maintenance_payments_received', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->foreignUuid('maintenance_invoice_id')->nullable()->constrained('maintenance_invoices')->nullOnDelete();
            $table->foreignUuid('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 3);
            $table->date('paid_on');
            $table->string('method', 40)->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['maintenance_team_id', 'paid_on']);
        });

        if (Schema::hasTable('maintenance_teams')) {
            $teams = DB::table('maintenance_teams')->get(['id', 'city', 'country_code']);
            foreach ($teams as $team) {
                DB::table('maintenance_team_cities')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'maintenance_team_id' => $team->id,
                    'city' => $team->city,
                    'country_code' => $team->country_code,
                    'region' => null,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_payments_received');
        Schema::dropIfExists('maintenance_invoices');
        Schema::dropIfExists('maintenance_team_cities');
    }
};
