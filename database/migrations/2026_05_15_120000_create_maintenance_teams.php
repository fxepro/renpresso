<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('city');
            $table->string('country_code', 2);
            $table->string('phone')->nullable();
            $table->boolean('is_listed')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['city', 'country_code', 'is_listed']);
        });

        Schema::create('landlord_maintenance_team', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('maintenance_team_id')->constrained('maintenance_teams')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['landlord_id', 'maintenance_team_id']);
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignUuid('maintenance_team_id')->nullable()->after('assignee_id')->constrained('maintenance_teams')->nullOnDelete();
        });

        if (Schema::hasTable('landlord_maintenance_staff')) {
            $links = DB::table('landlord_maintenance_staff')->get();
            foreach ($links as $link) {
                $owner = DB::table('users')->where('id', $link->staff_id)->first();
                if (! $owner || $owner->role !== 'maintenance') {
                    continue;
                }

                $teamId = DB::table('maintenance_teams')->where('owner_id', $link->staff_id)->value('id');
                if (! $teamId) {
                    $teamId = (string) \Illuminate\Support\Str::uuid();
                    DB::table('maintenance_teams')->insert([
                        'id'           => $teamId,
                        'owner_id'     => $link->staff_id,
                        'name'         => trim(($owner->first_name ?? 'Maintenance').' '.($owner->last_name ?? 'Team')),
                        'description'  => null,
                        'city'         => 'Unknown',
                        'country_code' => strtoupper($owner->home_country ?? 'US'),
                        'phone'        => $owner->phone,
                        'is_listed'    => true,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                DB::table('landlord_maintenance_team')->insertOrIgnore([
                    'landlord_id'         => $link->landlord_id,
                    'maintenance_team_id' => $teamId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maintenance_team_id');
        });
        Schema::dropIfExists('landlord_maintenance_team');
        Schema::dropIfExists('maintenance_teams');
    }
};
