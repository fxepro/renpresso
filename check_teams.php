<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaintenanceTeam;

$t = MaintenanceTeam::first();
echo "=== TEAM FIELDS ===\n";
foreach ($t->getAttributes() as $k => $v) {
    echo "  {$k}: ".substr((string)($v ?? 'null'), 0, 80)."\n";
}

echo "\n=== ALL TEAMS ===\n";
MaintenanceTeam::all()->each(function($t) {
    $svcs = is_array($t->services) ? implode(', ', $t->services) : $t->services;
    echo "  [{$t->country_code}] {$t->name} | listed:".($t->is_listed?'Y':'N')." | {$svcs}\n";
});

echo "\n=== RELATIONS ON TEAM MODEL ===\n";
$methods = get_class_methods($t);
$rels = array_filter($methods, fn($m) => !str_starts_with($m,'_') && !str_starts_with($m,'get') && !str_starts_with($m,'set') && !str_starts_with($m,'has') && strlen($m) > 3 && !in_array($m, ['fill','save','delete','create','update','find','first','where','with','load','fresh','refresh','toArray','toJson','jsonSerialize']));
echo implode(', ', array_values($rels))."\n";

echo "\n=== TEAM OWNER SAMPLE ===\n";
$full = MaintenanceTeam::with(['owner','reviews','cities'])->first();
echo "owner: ".($full->owner?->first_name.' '.$full->owner?->last_name ?? 'null')."\n";
echo "owner email: ".($full->owner?->email ?? 'null')."\n";
echo "reviews count: ".$full->reviews->count()."\n";
echo "cities: ".$full->cities->pluck('city')->implode(', ')."\n";

echo "\n=== SERVICES/COUNTRIES BREAKDOWN ===\n";
MaintenanceTeam::all()->groupBy('country_code')->each(function($teams, $country) {
    echo "  {$country}: ".$teams->count()." team(s)\n";
});

echo "\n=== LANDLORD-TEAM LINKS ===\n";
$links = \Illuminate\Support\Facades\DB::table('landlord_maintenance_team')->count();
echo "landlord_maintenance_team rows: {$links}\n";
$staff = \Illuminate\Support\Facades\DB::table('landlord_maintenance_staff')->count();
echo "landlord_maintenance_staff rows: {$staff}\n";
