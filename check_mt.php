<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaintenanceTeam;

$t = MaintenanceTeam::first();
echo "=== ALL FIELDS ===\n";
foreach ($t->getAttributes() as $k => $v) {
    echo "  {$k}: ".substr((string)($v ?? 'null'), 0, 80)."\n";
}

echo "\n=== RELATIONS ON MODEL ===\n";
$methods = (new ReflectionClass($t))->getMethods(ReflectionMethod::IS_PUBLIC);
foreach ($methods as $m) {
    if ($m->class === MaintenanceTeam::class && !str_starts_with($m->name,'_') && !str_starts_with($m->name,'get') && !str_starts_with($m->name,'set')) {
        echo "  ".$m->name."\n";
    }
}

echo "\n=== ALL 10 TEAMS ===\n";
MaintenanceTeam::withCount(['reviews'])->get()->each(function($t) {
    $svcs = is_array($t->services) ? implode(', ', $t->services) : $t->services;
    echo "  {$t->name} | {$t->country_code} | {$t->city} | reviews:{$t->reviews_count} | listed:".($t->is_listed?'yes':'no')."\n";
    echo "    services: {$svcs}\n";
});

echo "\n=== MAINTENANCE REQUESTS BY TEAM ===\n";
\Illuminate\Support\Facades\DB::table('maintenance_requests')
    ->whereNotNull('maintenance_team_id')
    ->selectRaw('maintenance_team_id, count(*) as cnt')
    ->groupBy('maintenance_team_id')
    ->get()
    ->each(fn($r) => print("  team:{$r->maintenance_team_id} reqs:{$r->cnt}\n"));

echo "Total unassigned requests: ".\App\Models\MaintenanceRequest::whereNull('maintenance_team_id')->count()."\n";
