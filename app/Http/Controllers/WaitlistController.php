<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistConfirmationMail;
use App\Models\WaitlistEmail;
use App\Support\WorldCountries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'              => 'required|email|max:255',
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'home_country'       => ['required', 'string', 'size:2', Rule::in(WorldCountries::codes())],
            'portfolio_size'     => 'nullable|string|max:100',
            'property_countries' => 'nullable|string|max:255',
            'pain_point'         => 'nullable|string|max:255',
        ]);

        $code = strtoupper($validated['home_country']);
        $validated['home_country'] = WorldCountries::name($code);

        $entry = WaitlistEmail::saveProfile($validated);

        try {
            Mail::to($entry->email)->send(new WaitlistConfirmationMail($entry));
        } catch (\Throwable $e) {
            Log::warning('Waitlist confirmation email failed', [
                'email' => $entry->email,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('waitlist_success', true);
    }
}
