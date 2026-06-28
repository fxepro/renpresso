<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCleaningTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CleaningAccountController extends Controller
{
    use ResolvesCleaningTeam;

    public function show(Request $request)
    {
        $user = Auth::user();
        $team = $this->cleaningTeam();
        $team?->loadCount('reviews');

        $tab = $request->query('tab', 'profile');
        if (! in_array($tab, ['profile', 'team', 'reviews'], true)) {
            $tab = 'profile';
        }

        $reviews = $team
            ? $team->reviews()->with('landlord')->latest()->get()
            : collect();

        return view('dashboard.cleaning-portal.account.index', compact('user', 'team', 'tab', 'reviews'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return redirect()
            ->route('clean.account', ['tab' => 'profile'])
            ->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('clean.account', ['tab' => 'profile'])
            ->with('success', 'Password updated.');
    }
}
