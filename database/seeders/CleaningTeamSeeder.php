<?php

namespace Database\Seeders;

use App\Models\CleaningTeam;
use App\Models\CleaningTeamReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CleaningTeamSeeder extends Seeder
{
    public function run(): void
    {
        $reviewerLandlords = collect([
            ['email' => 'landlord.seed1@rentersmaxx.com', 'first_name' => 'Nina', 'last_name' => 'Patel'],
            ['email' => 'landlord.seed2@rentersmaxx.com', 'first_name' => 'Marcus', 'last_name' => 'Webb'],
        ])->map(fn ($row) => User::firstOrCreate(['email' => $row['email']], [
            'first_name'   => $row['first_name'],
            'last_name'    => $row['last_name'],
            'password'     => bcrypt('password'),
            'role'         => 'landlord',
            'home_country' => 'US',
            'home_currency'=> 'USD',
        ]));

        $crews = [
            [
                'owner' => ['email' => 'crew.jakarta.turnover@rentersmaxx.com', 'first_name' => 'Rina', 'last_name' => 'Kusuma'],
                'team'  => [
                    'name'         => 'Kemang Turnover Clean',
                    'city'         => 'Jakarta',
                    'country_code' => 'ID',
                    'phone'        => '+62 21 5000 0101',
                    'description'  => 'Same-day turnover cleans for villas and apartments in Kemang. Linen optional, photo checklist after every visit.',
                    'services'     => ['Turnover clean', 'Linen', 'Deep clean'],
                ],
                'engage_demo' => true,
                'reviews' => [
                    [5, 'Guest-ready every checkout — reliable for our Kemang villa.'],
                    [4, 'Fast linen swap and kitchen reset.'],
                ],
            ],
            [
                'owner' => ['email' => 'crew.jakarta.sparkle@rentersmaxx.com', 'first_name' => 'Budi', 'last_name' => 'Pratama'],
                'team'  => [
                    'name'         => 'Jakarta Sparkle Crew',
                    'city'         => 'Jakarta',
                    'country_code' => 'ID',
                    'phone'        => '+62 21 5000 0102',
                    'description'  => 'Short-stay specialists — bathrooms, kitchens, and balcony resets between guests.',
                    'services'     => ['Turnover clean', 'Sanitise', 'Restocking'],
                ],
                'reviews' => [
                    [5, 'Consistent quality on back-to-back bookings.'],
                ],
            ],
            [
                'owner' => ['email' => 'crew.paris.fresh@rentersmaxx.com', 'first_name' => 'Sophie', 'last_name' => 'Martin'],
                'team'  => [
                    'name'         => 'Paris Fresh Stays',
                    'city'         => 'Paris',
                    'country_code' => 'FR',
                    'phone'        => '+33 1 42 00 01 01',
                    'description'  => 'Boutique turnover service for central Paris apartments. Bilingual coordinators, eco products on request.',
                    'services'     => ['Turnover clean', 'Laundry', 'Welcome pack'],
                ],
                'reviews' => [
                    [5, 'Apartment smelled and looked new for every guest.'],
                    [4, 'Good communication on tight checkout windows.'],
                ],
            ],
            [
                'owner' => ['email' => 'crew.london.checkout@rentersmaxx.com', 'first_name' => 'James', 'last_name' => 'Wright'],
                'team'  => [
                    'name'         => 'London Checkout Clean',
                    'city'         => 'London',
                    'country_code' => 'GB',
                    'phone'        => '+44 20 7000 0101',
                    'description'  => 'East and central London short-lets — checkout cleans, mid-stay refreshes, and inventory photos.',
                    'services'     => ['Turnover clean', 'Mid-stay refresh', 'Inventory photos'],
                ],
                'reviews' => [
                    [4, 'Solid turnover on our Shoreditch flat.'],
                ],
            ],
        ];

        $demoLandlord = User::where('email', 'demo@rentersmaxx.com')->first();
        $reviewerIndex = 0;

        foreach ($crews as $row) {
            $owner = User::firstOrCreate(['email' => $row['owner']['email']], [
                'first_name'    => $row['owner']['first_name'],
                'last_name'     => $row['owner']['last_name'],
                'password'      => bcrypt('password'),
                'role'          => 'cleaning',
                'home_country'  => $row['team']['country_code'],
                'home_currency' => 'USD',
            ]);

            $team = CleaningTeam::firstOrCreate(
                ['owner_id' => $owner->id],
                [
                    'name'         => $row['team']['name'],
                    'city'         => $row['team']['city'],
                    'country_code' => $row['team']['country_code'],
                    'phone'        => $row['team']['phone'] ?? null,
                    'description'  => $row['team']['description'] ?? null,
                    'is_listed'    => true,
                ]
            );

            $team->fill([
                'name'         => $row['team']['name'],
                'city'         => $row['team']['city'],
                'country_code' => $row['team']['country_code'],
                'phone'        => $row['team']['phone'] ?? null,
                'description'  => $row['team']['description'] ?? null,
                'services'     => $row['team']['services'] ?? [],
                'is_listed'    => true,
            ]);
            $team->save();

            $team->cities()->firstOrCreate(
                [
                    'city'         => $row['team']['city'],
                    'country_code' => $row['team']['country_code'],
                ],
                ['is_primary' => true]
            );
            $team->syncPrimaryCityFromRecord();

            foreach ($row['reviews'] as [$rating, $comment]) {
                $landlord = $reviewerLandlords[$reviewerIndex % $reviewerLandlords->count()];
                $reviewerIndex++;

                CleaningTeamReview::firstOrCreate(
                    [
                        'cleaning_team_id' => $team->id,
                        'landlord_id'      => $landlord->id,
                    ],
                    [
                        'rating'  => $rating,
                        'comment' => $comment,
                    ]
                );
            }

            if (! empty($row['engage_demo']) && $demoLandlord) {
                $demoLandlord->engagedCleaningTeams()->syncWithoutDetaching([$team->id]);
            }
        }

        $this->command?->info('Seeded '.count($crews).' cleaning crews (demo landlord roster link for Kemang Turnover Clean when demo user exists).');
    }
}
