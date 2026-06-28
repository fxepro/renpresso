<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCleaningTeam;
use Illuminate\Http\Request;

class CleaningTeamProfileController extends Controller
{
    use ResolvesCleaningTeam;

    public function edit()
    {
        $team = $this->cleaningTeamOrAbort();

        return view('dashboard.cleaning-portal.team.edit', compact('team'));
    }

    public function update(Request $request)
    {
        $team = $this->cleaningTeamOrAbort();

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

        return back()->with('success', 'Crew profile saved.');
    }
}
