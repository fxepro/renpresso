<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PublicListingController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'long-term');
        if (! in_array($tab, ['long-term', 'short-term', 'sublets', 'roommates'], true)) {
            $tab = 'long-term';
        }

        $query = Property::query()
            ->where('listing_visibility', 'public')
            ->with('media');

        match ($tab) {
            'short-term' => $query->where('rental_mode', 'short_term'),
            'sublets'    => $query->where('rental_mode', 'long_term')->where('sublet_allowed', true),
            'roommates'  => $query->where('rental_mode', 'long_term')
                ->where('sublet_allowed', true)
                ->where('bedrooms', '>=', 2),
            default      => $query->where('rental_mode', 'long_term'),
        };

        $this->applyListingFilters($query, $request);

        $properties = $query->orderBy('country_code')->orderBy('city')->orderBy('name')->paginate(12)->withQueryString();

        return view('pages.listings', compact('properties', 'tab'));
    }

    public function longTerm(Request $request)
    {
        $query = Property::query()
            ->where('listing_visibility', 'public')
            ->where('rental_mode', 'long_term')
            ->with('media');

        $this->applyListingFilters($query, $request);

        $properties = $query->orderBy('country_code')->orderBy('city')->orderBy('name')->paginate(12)->withQueryString();

        return view('pages.listings-long-term', [
            'properties' => $properties,
            'mode'       => 'long_term',
        ]);
    }

    public function shortTerm(Request $request)
    {
        $query = Property::query()
            ->where('listing_visibility', 'public')
            ->where('rental_mode', 'short_term')
            ->with('media');

        $this->applyListingFilters($query, $request);

        $properties = $query->orderBy('country_code')->orderBy('city')->orderBy('name')->paginate(12)->withQueryString();

        return view('pages.listings-short-term', [
            'properties' => $properties,
            'mode'       => 'short_term',
        ]);
    }

    public function showLongTerm(Property $property)
    {
        abort_unless(
            $property->listing_visibility === 'public' && $property->rental_mode === 'long_term',
            404
        );

        $property->loadMissing('media');

        return view('pages.listings-long-term-show', ['property' => $property]);
    }

    public function showShortTerm(Property $property)
    {
        abort_unless(
            $property->listing_visibility === 'public' && $property->rental_mode === 'short_term',
            404
        );

        $property->loadMissing('media');

        return view('pages.listings-short-term-show', ['property' => $property]);
    }

    protected function applyListingFilters($query, Request $request): void
    {
        $country = strtoupper(trim((string) $request->query('country', '')));
        if (strlen($country) === 2 && array_key_exists($country, config('countries', []))) {
            $query->where('country_code', $country);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('city', 'like', $like);
            });
        }
    }
}
