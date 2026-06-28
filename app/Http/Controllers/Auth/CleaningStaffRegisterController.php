<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CleaningStaffInvite;
use App\Models\CleaningTeam;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CleaningStaffRegisterController extends Controller
{
    public function show(?string $invite_token = null)
    {
        if (Auth::check()) {
            return redirect()->route('clean.dashboard');
        }

        $invite = null;
        if ($invite_token) {
            $invite = CleaningStaffInvite::query()->where('token', $invite_token)->first();
            if (! $invite || ! $invite->isUsable()) {
                return view('pages.register-cleaning', [
                    'invite'       => null,
                    'invite_token' => null,
                    'inviteError'  => 'This invite link is invalid or has expired. You can still register your crew below — landlords in your city can find you in the directory.',
                ]);
            }
        }

        return view('pages.register-cleaning', [
            'invite'       => $invite,
            'invite_token' => $invite ? $invite->token : null,
            'inviteError'  => null,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('clean.dashboard');
        }

        $invite = null;
        if ($request->filled('invite_token')) {
            $invite = CleaningStaffInvite::query()->where('token', $request->input('invite_token'))->first();
            if (! $invite || ! $invite->isUsable()) {
                return back()->withInput()->withErrors(['invite_token' => 'This invite is invalid or has expired.']);
            }
        }

        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|min:8|confirmed',
            'invite_token'  => 'nullable|string|max:64',
            'team_name'     => 'required|string|max:255',
            'city'          => 'required|string|max:100',
            'country_code'  => 'required|string|size:2',
            'description'   => 'nullable|string|max:2000',
            'phone'         => 'nullable|string|max:30',
            'services'      => 'nullable|string|max:500',
        ]);

        if ($invite && strcasecmp($invite->email, $validated['email']) !== 0) {
            return back()->withInput()->withErrors([
                'email' => 'Use the same email your landlord invited: '.$invite->email,
            ]);
        }

        $user = User::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => strtolower($validated['email']),
            'password'     => Hash::make($validated['password']),
            'role'         => 'cleaning',
            'home_country' => strtoupper($validated['country_code']),
        ]);

        $services = isset($validated['services'])
            ? array_values(array_filter(array_map('trim', explode(',', $validated['services']))))
            : null;

        $team = CleaningTeam::create([
            'owner_id'     => $user->id,
            'name'         => $validated['team_name'],
            'description'  => $validated['description'] ?? null,
            'city'         => trim($validated['city']),
            'country_code' => strtoupper($validated['country_code']),
            'phone'        => $validated['phone'] ?? null,
            'services'     => $services ?: null,
            'is_listed'    => true,
        ]);

        $team->cities()->create([
            'city'         => $team->city,
            'country_code' => $team->country_code,
            'is_primary'   => true,
        ]);

        if ($invite) {
            $invite->landlord->engagedCleaningTeams()->syncWithoutDetaching([$team->id]);
            $invite->update([
                'used_at'       => now(),
                'staff_user_id' => $user->id,
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        $message = $invite
            ? 'Your crew is live and connected to '.$invite->landlord->fullName().'.'
            : 'Your crew is listed in '.$team->locationLabel().'. Landlords with properties there can add you to their roster.';

        return redirect()->route('clean.dashboard')->with('success', $message);
    }
}
