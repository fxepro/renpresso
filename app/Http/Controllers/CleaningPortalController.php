<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCleaningTeam;
use Illuminate\Support\Facades\Auth;

class CleaningPortalController extends Controller
{
    use ResolvesCleaningTeam;

    public function dashboard()
    {
        $team = $this->cleaningTeam();

        $stats = [
            'landlords_linked' => 0,
            'cities'           => 0,
            'reviews'          => 0,
            'avg_rating'       => null,
        ];

        if ($team) {
            $stats['landlords_linked'] = $team->engagedLandlordIds()->count();
            $stats['cities'] = $team->cities()->count();
            $stats['reviews'] = $team->reviews()->count();
            $stats['avg_rating'] = $team->averageRating();
        }

        $recentReviews = $team
            ? $team->reviews()->with('landlord')->latest()->limit(5)->get()
            : collect();

        return view('dashboard.cleaning-portal.dashboard', compact('team', 'stats', 'recentReviews'));
    }
}
