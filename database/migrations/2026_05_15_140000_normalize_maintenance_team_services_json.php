<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $teams = DB::table('maintenance_teams')->whereNotNull('services')->get(['id', 'services']);

        foreach ($teams as $team) {
            $services = $team->services;

            if (is_string($services)) {
                $decoded = json_decode($services, true);
                if (is_array($decoded)) {
                    DB::table('maintenance_teams')->where('id', $team->id)->update([
                        'services' => json_encode(array_values($decoded)),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
