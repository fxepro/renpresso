<?php

namespace App\Http\Controllers;

use App\Models\CleaningStaffInvite;
use App\Models\CleaningTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandlordCleaningTeamController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $landlord = Auth::user();
        $myTeamIds = $landlord->engagedCleaningTeams()->pluck('cleaning_teams.id');

        $myTeams = $landlord->engagedCleaningTeams()
            ->with('owner')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('name')
            ->get();

        $propertyLocations = $landlord->properties()
            ->select('city', 'country_code')
            ->distinct()
            ->get()
            ->map(fn ($p) => [
                'city'         => trim($p->city),
                'country_code' => strtoupper($p->country_code),
            ])
            ->unique(fn ($loc) => $loc['city'].'|'.$loc['country_code'])
            ->values();

        $browseTeams = collect();
        $search = trim((string) $request->query('q', ''));
        $cityFilter = $request->query('city');

        if ($propertyLocations->isNotEmpty()) {
            $browseTeams = CleaningTeam::query()
                ->with('owner')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_listed', true)
                ->whereNotIn('id', $myTeamIds)
                ->matchingPropertyLocations($propertyLocations)
                ->when($cityFilter, function ($query, $cityFilter) {
                    $query->where(function ($q) use ($cityFilter) {
                        $q->whereRaw('LOWER(cleaning_teams.city) = ?', [strtolower($cityFilter)])
                            ->orWhereHas('cities', fn ($c) => $c->whereRaw('LOWER(city) = ?', [strtolower($cityFilter)]));
                    });
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%')
                            ->orWhere('city', 'like', '%'.$search.'%');
                    });
                })
                ->orderByDesc('reviews_avg_rating')
                ->orderBy('name')
                ->get();
        }

        $cityOptions = $propertyLocations->pluck('city')->unique()->sort()->values();

        $activeTab = in_array($request->query('tab'), ['roster', 'discover'], true)
            ? $request->query('tab')
            : 'roster';

        if ($request->hasAny(['q', 'city']) || $activeTab === 'discover') {
            $activeTab = 'discover';
        }

        return view('dashboard.cleaning-team.index', compact(
            'myTeams',
            'browseTeams',
            'propertyLocations',
            'cityOptions',
            'search',
            'cityFilter',
            'activeTab',
        ));
    }

    public function show(CleaningTeam $team)
    {
        abort_unless(Auth::user()->isLandlord(), 403);
        abort_unless($team->is_listed, 404);

        $team->load(['owner', 'reviews' => fn ($q) => $q->with('landlord')->latest()]);
        $landlord = Auth::user();
        $isEngaged = $landlord->engagedCleaningTeams()->whereKey($team->id)->exists();
        $myReview = $team->reviews->firstWhere('landlord_id', $landlord->id);
        $matchesPortfolio = $landlord->properties()
            ->get()
            ->contains(fn ($property) => $team->coversCityCountry($property->city, $property->country_code));

        return view('dashboard.cleaning-team.show', compact(
            'team',
            'isEngaged',
            'myReview',
            'matchesPortfolio',
        ));
    }

    public function storeReview(Request $request, CleaningTeam $team)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $landlord = Auth::user();
        abort_unless(
            $landlord->engagedCleaningTeams()->whereKey($team->id)->exists(),
            403,
            'You can only review crews on your roster.'
        );

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $team->reviews()->updateOrCreate(
            ['landlord_id' => $landlord->id],
            [
                'rating'  => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('landlord.cleaning-team.show', $team)
            ->with('success', 'Your review was saved.');
    }

    public function engage(CleaningTeam $team)
    {
        abort_unless(Auth::user()->isLandlord(), 403);
        abort_unless($team->is_listed, 404);

        $landlord = Auth::user();
        $matchesPortfolio = $landlord->properties()
            ->get()
            ->contains(fn ($property) => $team->coversCityCountry($property->city, $property->country_code));

        abort_unless($matchesPortfolio, 422, 'This crew is not listed in a city where you have properties.');

        $landlord->engagedCleaningTeams()->syncWithoutDetaching([$team->id]);

        return redirect()
            ->route('landlord.cleaning-team.show', $team)
            ->with('success', $team->name.' added to your roster.');
    }

    public function disengage(CleaningTeam $team)
    {
        abort_unless(Auth::user()->isLandlord(), 403);

        $detached = Auth::user()->engagedCleaningTeams()->detach($team->id);
        abort_unless($detached > 0, 404);

        return redirect()
            ->route('landlord.cleaning-team.index')
            ->with('success', 'Removed '.$team->name.' from your roster.');
    }

    public function storeInvite(Request $request)
    {
        abort_unless(Auth::user()->isLandlord(), 403);
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $invite = CleaningStaffInvite::create([
            'landlord_id' => Auth::id(),
            'email'       => strtolower($validated['email']),
            'expires_at'  => now()->addDays(14),
        ]);

        $url = route('register.cleaning', ['invite_token' => $invite->token]);

        return back()
            ->with('success', 'Invite link created.')
            ->with('invite_created_url', $url)
            ->with('show_invite_panel', true);
    }
}
