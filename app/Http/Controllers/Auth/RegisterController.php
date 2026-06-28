<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LandlordProfile;
use App\Models\User;
use App\Support\WorldCountries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('pages.register');
    }

    public function store(Request $request)
    {
        if (Auth::check()) {
            return redirect()
                ->route('register')
                ->withErrors(['email' => 'You are already signed in. Sign out first to create a new account.']);
        }

        $validator = Validator::make($request->all(), [
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|min:8|confirmed',
            'home_country'          => ['required', 'string', 'size:2', Rule::in(WorldCountries::codes())],
            'portfolio_size'        => 'nullable|string|max:100',
            'property_countries'    => 'nullable|string|max:255',
            'pain_point'            => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('register')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $code      = strtoupper($validated['home_country']);

        $user = User::create([
            'first_name'              => $validated['first_name'],
            'last_name'               => $validated['last_name'],
            'email'                   => $validated['email'],
            'password'                => $validated['password'],
            'home_country'            => $code,
            'role'                    => 'landlord',
            'landlord_account_status' => 'pending_activation',
        ]);

        LandlordProfile::create([
            'user_id'             => $user->id,
            'residence_country'   => WorldCountries::name($code),
            'portfolio_size'      => $validated['portfolio_size'] ?? null,
            'property_countries'  => $validated['property_countries'] ?? null,
            'pain_point'          => $validated['pain_point'] ?? null,
        ]);

        return redirect()
            ->route('register')
            ->with('signup_success', true);
    }
}
