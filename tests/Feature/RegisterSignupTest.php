<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\User;
use App\Models\WaitlistEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_creates_user_and_landlord_profile_only(): void
    {
        $email = 'signup-' . time() . '@example.com';

        $response = $this->post(route('auth.register'), [
            'first_name'            => 'Alex',
            'last_name'             => 'Johnson',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'home_country'          => 'US',
            'portfolio_size'        => '1 property',
            'property_countries'    => 'Texas',
            'pain_point'            => 'Chasing rent across time zones',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('signup_success', true);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame('US', $user->home_country);
        $this->assertSame('landlord', $user->role);

        $this->assertDatabaseHas('landlord_profiles', [
            'user_id'            => $user->id,
            'residence_country'  => 'United States',
            'property_countries' => 'Texas',
            'portfolio_size'     => '1 property',
        ]);

        $this->assertDatabaseMissing('waitlist_emails', ['email' => $email]);
    }

    public function test_signup_treats_all_countries_equally(): void
    {
        $email = 'fr-' . time() . '@example.com';

        $response = $this->post(route('auth.register'), [
            'first_name'            => 'Marie',
            'last_name'             => 'Dupont',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'home_country'          => 'FR',
            'property_countries'    => 'Paris',
        ]);

        $response->assertRedirect(route('register'));
        $this->assertSame('FR', User::where('email', $email)->value('home_country'));
        $this->assertSame('France', LandlordProfile::whereHas('user', fn ($q) => $q->where('email', $email))->value('residence_country'));
    }

    public function test_signup_rejects_invalid_country_code(): void
    {
        $response = $this->from(route('register'))->post(route('auth.register'), [
            'first_name'            => 'Alex',
            'last_name'             => 'Johnson',
            'email'                 => 'bad-country-' . time() . '@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'home_country'          => 'Q1',
            'property_countries'    => 'Somewhere',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('home_country');
    }

    public function test_waitlist_stays_separate_from_signup(): void
    {
        $this->post('/waitlist', [
            'email'              => 'waitlist-only@example.com',
            'first_name'         => 'Pat',
            'last_name'          => 'Lee',
            'home_country'       => 'CM',
            'property_countries' => 'Douala',
        ]);

        $this->assertDatabaseHas('waitlist_emails', [
            'email'        => 'waitlist-only@example.com',
            'home_country' => 'Cameroon',
        ]);
        $this->assertNull(User::where('email', 'waitlist-only@example.com')->first());
    }
}
