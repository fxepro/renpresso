<?php

namespace Database\Seeders;

use App\Models\MaintenanceTeam;
use App\Models\MaintenanceTeamReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Clear bogus "verified" flags left by older seeds (verified without an ID file).
        User::query()
            ->where('role', 'maintenance')
            ->where('kyc_verified', true)
            ->where(function ($q) {
                $q->whereNull('kyc_id_document_path')->orWhere('kyc_status', '!=', 'verified');
            })
            ->update([
                'kyc_verified'    => false,
                'kyc_verified_at' => null,
                'kyc_status'      => 'none',
            ]);

        User::query()
            ->where('role', 'maintenance')
            ->with('ownedMaintenanceTeam')
            ->each(function (User $owner) {
                $team = $owner->ownedMaintenanceTeam;
                if (! $team || ! $owner->kyc_legal_name) {
                    return;
                }
                if ($owner->kycLegalNameIsCompanyName($owner->kyc_legal_name, $team)) {
                    $owner->update(['kyc_legal_name' => null]);
                }
            });

        $reviewerLandlords = collect([
            ['email' => 'landlord.seed1@rentersmaxx.com', 'first_name' => 'Nina', 'last_name' => 'Patel'],
            ['email' => 'landlord.seed2@rentersmaxx.com', 'first_name' => 'Marcus', 'last_name' => 'Webb'],
            ['email' => 'landlord.seed3@rentersmaxx.com', 'first_name' => 'Elena', 'last_name' => 'Rossi'],
        ])->map(fn ($row) => User::firstOrCreate(['email' => $row['email']], [
            'first_name'   => $row['first_name'],
            'last_name'    => $row['last_name'],
            'password'     => bcrypt('password'),
            'role'         => 'landlord',
            'home_country' => 'US',
            'home_currency'=> 'USD',
        ]));

        $teams = [
            [
                'owner' => ['email' => 'team.paris.care@rentersmaxx.com', 'first_name' => 'Luc', 'last_name' => 'Bernard'],
                'team'  => [
                    'name'        => 'Paris Property Care',
                    'city'        => 'Paris',
                    'country_code'=> 'FR',
                    'phone'       => '+33 1 42 00 00 01',
                    'description' => 'Full-service maintenance for apartments and haussmann buildings in central Paris. Same-day emergency response, bilingual team, transparent quotes before work begins.',
                    'services'    => ['Plumbing', 'Electrical', 'HVAC', 'General repairs'],
                ],
                'kyc' => ['kyc_legal_name' => 'Paris Property Care SARL', 'kyc_city' => 'Paris', 'kyc_region' => 'Île-de-France', 'kyc_address_country' => 'FR'],
                'reviews' => [
                    [5, 'Fixed a leaking tap within hours. Professional and tidy.'],
                    [5, 'Our go-to team for Rue de Rivoli — always on time.'],
                    [4, 'Good work on electrical; quote was clear upfront.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.paris.rivoli@rentersmaxx.com', 'first_name' => 'Camille', 'last_name' => 'Dupont'],
                'team'  => [
                    'name'        => 'Rivoli Maintenance Co.',
                    'city'        => 'Paris',
                    'country_code'=> 'FR',
                    'phone'       => '+33 1 42 00 00 02',
                    'description' => 'Specialists in rental turnovers, snagging lists, and tenant move-in/out repairs across Paris 1–12.',
                    'services'    => ['Turnovers', 'Painting', 'Plumbing', 'Appliance install'],
                ],
                'kyc' => ['kyc_legal_name' => 'Rivoli Maintenance Co. SAS', 'kyc_city' => 'Paris', 'kyc_region' => 'Île-de-France', 'kyc_address_country' => 'FR', 'kyc_status' => 'pending', 'kyc_verified' => false],
                'reviews' => [
                    [4, 'Solid turnover service — property was ready ahead of schedule.'],
                    [5, 'Excellent communication with our property manager.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.mumbai.fix@rentersmaxx.com', 'first_name' => 'Rahul', 'last_name' => 'Mehta'],
                'team'  => [
                    'name'        => 'Bandra Fix-It Squad',
                    'city'        => 'Mumbai',
                    'country_code'=> 'IN',
                    'phone'       => '+91 22 4000 0001',
                    'description' => 'Trusted maintenance for Bandra and western suburbs. UPI invoicing, WhatsApp updates, and vetted electricians on call.',
                    'services'    => ['Electrical', 'Plumbing', 'AC service', 'Pest control'],
                ],
                'kyc' => ['kyc_legal_name' => 'Bandra Fix-It Services Pvt Ltd', 'kyc_city' => 'Mumbai', 'kyc_region' => 'Maharashtra', 'kyc_address_country' => 'IN'],
                'engage_demo' => true,
                'reviews' => [
                    [5, 'Resolved power socket issue same day.'],
                    [4, 'Reliable for monsoon-season plumbing checks.'],
                    [5, 'Tenant was happy with AC service.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.mumbai.hill@rentersmaxx.com', 'first_name' => 'Anita', 'last_name' => 'Desai'],
                'team'  => [
                    'name'        => 'Hill Road Home Services',
                    'city'        => 'Mumbai',
                    'country_code'=> 'IN',
                    'phone'       => '+91 22 4000 0002',
                    'description' => 'Family-run team covering repairs, deep cleans between tenants, and appliance servicing.',
                    'services'    => ['Cleaning', 'Plumbing', 'Appliances', 'Handyman'],
                ],
                'kyc' => ['kyc_legal_name' => 'Hill Road Home Services', 'kyc_city' => 'Mumbai', 'kyc_region' => 'Maharashtra', 'kyc_address_country' => 'IN'],
                'reviews' => [
                    [4, 'Deep clean was thorough; minor delay on arrival.'],
                    [4, 'Fair pricing for handyman jobs.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.london.shoreditch@rentersmaxx.com', 'first_name' => 'Tom', 'last_name' => 'Harris'],
                'team'  => [
                    'name'        => 'Shoreditch Property Ops',
                    'city'        => 'London',
                    'country_code'=> 'GB',
                    'phone'       => '+44 20 7000 0001',
                    'description' => 'East London maintenance for studios and HMOs. Gas Safe engineers, 24/7 emergency line, digital job reports after every visit.',
                    'services'    => ['Gas & heating', 'Electrical', 'Emergency', 'Compliance checks'],
                ],
                'kyc' => ['kyc_legal_name' => 'Shoreditch Property Ops Ltd', 'kyc_city' => 'London', 'kyc_region' => 'Greater London', 'kyc_address_country' => 'GB'],
                'reviews' => [
                    [5, 'Gas safety certificate handled smoothly.'],
                    [5, 'Clear post-visit reports — helpful for our records.'],
                    [4, 'Quick emergency call-out on a Sunday.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.london.curtain@rentersmaxx.com', 'first_name' => 'Sarah', 'last_name' => 'Clarke'],
                'team'  => [
                    'name'        => 'Curtain Road Repairs',
                    'city'        => 'London',
                    'country_code'=> 'GB',
                    'phone'       => '+44 20 7000 0002',
                    'description' => 'Boutique maintenance for creative-industry rentals. Sensitive to tenant schedules and noise restrictions.',
                    'services'    => ['Snagging', 'Painting', 'Locks & security', 'General repairs'],
                ],
                'kyc' => ['kyc_legal_name' => 'Curtain Road Repairs Ltd', 'kyc_city' => 'London', 'kyc_region' => 'Greater London', 'kyc_address_country' => 'GB'],
                'reviews' => [
                    [5, 'Respectful of tenant work-from-home hours.'],
                    [4, 'Good snagging work on a new lease.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.lagos.lekki@rentersmaxx.com', 'first_name' => 'Tunde', 'last_name' => 'Adeyemi'],
                'team'  => [
                    'name'        => 'Lekki Estate Maintenance',
                    'city'        => 'Lagos',
                    'country_code'=> 'NG',
                    'phone'       => '+234 1 400 0001',
                    'description' => 'Generator-aware maintenance for Lekki and VI properties. Water pumping, gate motors, and full villa upkeep.',
                    'services'    => ['Generator', 'Plumbing', 'Security systems', 'Landscaping'],
                ],
                'kyc' => ['kyc_legal_name' => 'Lekki Estate Maintenance Ltd', 'kyc_city' => 'Lagos', 'kyc_region' => 'Lagos State', 'kyc_address_country' => 'NG'],
                'reviews' => [
                    [5, 'Generator serviced before rainy season — no downtime.'],
                    [4, 'Responsive on WhatsApp.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.lagos.admiralty@rentersmaxx.com', 'first_name' => 'Chioma', 'last_name' => 'Okonkwo'],
                'team'  => [
                    'name'        => 'Admiralty Way Home Team',
                    'city'        => 'Lagos',
                    'country_code'=> 'NG',
                    'phone'       => '+234 1 400 0002',
                    'description' => 'Move-in ready packages: paint touch-ups, AC checks, and appliance testing for new tenants.',
                    'services'    => ['Turnovers', 'AC', 'Painting', 'Appliances'],
                ],
                'kyc' => ['kyc_legal_name' => 'Admiralty Way Home Team', 'kyc_city' => 'Lagos', 'kyc_region' => 'Lagos State', 'kyc_address_country' => 'NG'],
                'reviews' => [
                    [4, 'Turnover package saved us a week of coordination.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.jakarta.kemang@rentersmaxx.com', 'first_name' => 'Andi', 'last_name' => 'Wijaya'],
                'team'  => [
                    'name'        => 'Kemang Villa Care',
                    'city'        => 'Jakarta',
                    'country_code'=> 'ID',
                    'phone'       => '+62 21 5000 0001',
                    'description' => 'Villa and townhouse maintenance in Kemang. Pool checks, tropical humidity issues, and staff coordination for absentee landlords.',
                    'services'    => ['Pool', 'Plumbing', 'Electrical', 'Garden'],
                ],
                'kyc' => ['kyc_legal_name' => 'Kemang Villa Care PT', 'kyc_city' => 'Jakarta', 'kyc_region' => 'DKI Jakarta', 'kyc_address_country' => 'ID'],
                'reviews' => [
                    [5, 'Pool and garden always guest-ready.'],
                    [5, 'Proactive on humidity-related mould issues.'],
                ],
            ],
            [
                'owner' => ['email' => 'team.jakarta.raya@rentersmaxx.com', 'first_name' => 'Dewi', 'last_name' => 'Santoso'],
                'team'  => [
                    'name'        => 'Kemang Raya Repairs',
                    'city'        => 'Jakarta',
                    'country_code'=> 'ID',
                    'phone'       => '+62 21 5000 0002',
                    'description' => 'Fast repairs for expat rentals. English-speaking coordinators, weekend availability.',
                    'services'    => ['Emergency', 'Plumbing', 'Electrical', 'Appliances'],
                ],
                'kyc' => ['kyc_legal_name' => 'Kemang Raya Repairs', 'kyc_city' => 'Jakarta', 'kyc_region' => 'DKI Jakarta', 'kyc_address_country' => 'ID', 'kyc_verified' => false, 'kyc_status' => 'none'],
                'reviews' => [
                    [4, 'Weekend availability was a lifesaver.'],
                ],
            ],
        ];

        $reviewerIndex = 0;
        foreach ($teams as $row) {
            $owner = User::firstOrCreate(['email' => $row['owner']['email']], [
                'first_name'    => $row['owner']['first_name'],
                'last_name'     => $row['owner']['last_name'],
                'password'      => bcrypt('password'),
                'role'          => 'maintenance',
                'home_country'  => $row['team']['country_code'],
                'home_currency' => 'USD',
            ]);

            $personLegalName = trim($row['owner']['first_name'].' '.$row['owner']['last_name']);
            $kycOverrides = $row['kyc'] ?? [];
            unset($kycOverrides['kyc_legal_name']);

            $kyc = array_merge([
                'kyc_legal_name'      => $personLegalName,
                'kyc_verified'        => false,
                'kyc_verified_at'     => null,
                'kyc_status'          => 'none',
                'kyc_submitted_at'    => null,
                'kyc_rejection_reason'=> null,
            ], $kycOverrides);

            $owner->update($kyc);

            $team = MaintenanceTeam::firstOrCreate(
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

                MaintenanceTeamReview::firstOrCreate(
                    [
                        'maintenance_team_id' => $team->id,
                        'landlord_id'         => $landlord->id,
                    ],
                    [
                        'rating'  => $rating,
                        'comment' => $comment,
                    ]
                );
            }

        }

        $this->command?->info('Seeded '.count($teams).' maintenance teams with reviews (roster links are created by landlords in the app).');
    }
}
