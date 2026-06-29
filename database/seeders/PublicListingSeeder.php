<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Marketing-site demo listings (public directory only).
 * Safe to run on production — skips properties that already exist by name.
 *
 *   php artisan db:seed --class=PublicListingSeeder
 */
class PublicListingSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::firstOrCreate(['email' => 'demo@renpresso.com'], [
            'first_name'    => 'Alex',
            'last_name'     => 'Morgan',
            'password'      => bcrypt('password'),
            'role'          => 'landlord',
            'home_country'  => 'US',
            'home_currency' => 'USD',
        ]);

        $listings = [
            [
                'name'               => 'Brooklyn Heights 2BR',
                'address_line1'      => '45 Montague Street',
                'city'               => 'Brooklyn',
                'postal_code'        => '11201',
                'country_code'       => 'US',
                'currency_code'      => 'USD',
                'processor_slug'     => 'stripe',
                'type'               => 'apartment',
                'occupancy_mode'     => 'single',
                'bedrooms'           => 2,
                'rental_mode'        => 'long_term',
                'listing_visibility' => 'public',
                'rent_minor_units'   => 320000,
            ],
            [
                'name'               => 'Austin East Side Duplex',
                'address_line1'      => '1208 E Cesar Chavez St',
                'city'               => 'Austin',
                'postal_code'        => '78702',
                'country_code'       => 'US',
                'currency_code'      => 'USD',
                'processor_slug'     => 'stripe',
                'type'               => 'house',
                'occupancy_mode'     => 'single',
                'bedrooms'           => 3,
                'rental_mode'        => 'long_term',
                'listing_visibility' => 'public',
                'rent_minor_units'   => 245000,
            ],
            [
                'name'               => 'Denver Capitol Hill Studio',
                'address_line1'      => '777 E 17th Ave',
                'city'               => 'Denver',
                'postal_code'        => '80203',
                'country_code'       => 'US',
                'currency_code'      => 'USD',
                'processor_slug'     => 'stripe',
                'type'               => 'apartment',
                'occupancy_mode'     => 'single',
                'bedrooms'           => 1,
                'rental_mode'        => 'long_term',
                'listing_visibility' => 'public',
                'rent_minor_units'   => 165000,
            ],
            [
                'name'               => 'Miami Beach Short Stay',
                'address_line1'      => '1500 Ocean Drive',
                'city'               => 'Miami Beach',
                'postal_code'        => '33139',
                'country_code'       => 'US',
                'currency_code'      => 'USD',
                'processor_slug'     => 'stripe',
                'type'               => 'apartment',
                'occupancy_mode'     => 'single',
                'bedrooms'           => 1,
                'rental_mode'        => 'short_term',
                'listing_visibility' => 'public',
                'rent_minor_units'   => 18500,
            ],
        ];

        foreach ($listings as $data) {
            if ($landlord->properties()->where('name', $data['name'])->exists()) {
                $this->command?->warn("Skipping {$data['name']} — already exists.");

                continue;
            }

            $landlord->properties()->create($data);
            $this->command?->info("Created public listing: {$data['name']}");
        }

        $this->command?->info('Done. Sign in as demo@renpresso.com / password to manage listings.');
    }
}
