<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceTeamProfileController extends Controller
{
    use ResolvesMaintenanceTeam;

    public function edit()
    {
        return redirect()->route('maint.account', ['tab' => 'company', 'sec' => 'overview']);
    }

    public function update(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'services' => ['nullable', 'string', 'max:500'],
            'is_listed' => ['sometimes', 'boolean'],
        ]);

        $services = isset($data['services'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['services']))))
            : null;

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'] ?? null,
            'services' => $services ?: null,
            'is_listed' => $request->boolean('is_listed'),
        ]);

        return back()->with('success', 'Team profile saved.');
    }
}
