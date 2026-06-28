<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;
use App\Models\MaintenanceTeamCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceCityController extends Controller
{
    use ResolvesMaintenanceTeam;

    public function index(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();
        $cities = $team->cities()->orderByDesc('is_primary')->orderBy('city')->get();

        $editingCity = null;
        if ($request->filled('edit')) {
            $editingCity = $team->cities()->where('id', $request->query('edit'))->first();
        }

        return view('dashboard.maintenance-portal.cities.index', compact('team', 'cities', 'editingCity'));
    }

    public function store(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();

        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'region' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $data['country_code'] = strtoupper($data['country_code']);
        $data['city'] = trim($data['city']);
        $isPrimary = (bool) ($data['is_primary'] ?? false);

        DB::transaction(function () use ($team, $data, $isPrimary) {
            if ($isPrimary) {
                $team->cities()->update(['is_primary' => false]);
            }

            $team->cities()->create([
                'city' => $data['city'],
                'country_code' => $data['country_code'],
                'region' => $data['region'] ?? null,
                'is_primary' => $isPrimary || $team->cities()->count() === 0,
            ]);

            $team->syncPrimaryCityFromRecord();
        });

        return back()->with('success', 'Service city added.');
    }

    public function update(Request $request, MaintenanceTeamCity $city)
    {
        $team = $this->maintenanceTeamOrAbort();
        abort_unless($city->maintenance_team_id === $team->id, 404);

        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],
            'region' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $data['country_code'] = strtoupper($data['country_code']);
        $data['city'] = trim($data['city']);
        $isPrimary = (bool) ($data['is_primary'] ?? false);

        DB::transaction(function () use ($team, $city, $data, $isPrimary) {
            if ($isPrimary) {
                $team->cities()->where('id', '!=', $city->id)->update(['is_primary' => false]);
            }

            $city->update([
                'city' => $data['city'],
                'country_code' => $data['country_code'],
                'region' => $data['region'] ?? null,
                'is_primary' => $isPrimary,
            ]);

            $team->syncPrimaryCityFromRecord();
        });

        return redirect()->route('maint.cities.index')->with('success', 'Operating city updated.');
    }

    public function destroy(MaintenanceTeamCity $city)
    {
        $team = $this->maintenanceTeamOrAbort();
        abort_unless($city->maintenance_team_id === $team->id, 404);
        abort_if($team->cities()->count() <= 1, 422, 'Keep at least one service city.');

        $wasPrimary = $city->is_primary;
        $city->delete();

        if ($wasPrimary) {
            $team->cities()->orderBy('city')->first()?->update(['is_primary' => true]);
            $team->syncPrimaryCityFromRecord();
        }

        return back()->with('success', 'City removed.');
    }
}
