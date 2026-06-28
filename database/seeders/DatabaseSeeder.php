<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\WaitlistEmail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['email' => 'admin@rentersmaxx.com', 'first_name' => 'Platform', 'last_name' => 'Admin'],
            ['email' => 'finance@rentersmaxx.com', 'first_name' => 'Finance', 'last_name' => 'Ops'],
            ['email' => 'ops@rentersmaxx.com', 'first_name' => 'Operations', 'last_name' => 'Admin'],
        ] as $admin) {
            User::firstOrCreate(['email' => $admin['email']], [
                'first_name'    => $admin['first_name'],
                'last_name'     => $admin['last_name'],
                'password'      => bcrypt('password'),
                'role'          => 'admin',
                'home_country'  => 'US',
                'home_currency' => 'USD',
            ]);
        }

        $landlord = User::firstOrCreate(['email' => 'demo@rentersmaxx.com'], [
            'first_name' => 'Alex',
            'last_name'  => 'Morgan',
            'password'   => bcrypt('password'),
            'role'       => 'landlord',
            'home_country'  => 'US',
            'home_currency' => 'USD',
        ]);

        $portfolio = [
            [
                'property' => [
                    'name' => 'Rue de Rivoli Apartment', 'address_line1' => '42 Rue de Rivoli', 'city' => 'Paris',
                    'country_code' => 'FR', 'currency_code' => 'EUR', 'processor_slug' => 'stripe', 'type' => 'apartment',
                    'occupancy_mode' => 'single', 'bedrooms' => 2, 'postal_code' => '75001',
                ],
                'tenant' => ['first_name' => 'Sophie', 'last_name' => 'Dubois', 'email' => 'sophie@example.com'],
                'lease'  => ['rent' => 150000, 'due_day' => 1, 'start' => '-8 months'],
                'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success'], ['m' => 3, 's' => 'success'], ['m' => 4, 's' => 'success'], ['m' => 5, 's' => 'failed'], ['m' => 6, 's' => 'success']],
                'fx' => 1080000,
                'maintenance' => ['plumbing', 'Leaking tap in bathroom', 'The bathroom tap drips constantly.', 'acknowledged'],
            ],
            [
                'property' => [
                    'name' => 'Bandra West Flat', 'address_line1' => '14 Hill Road', 'city' => 'Mumbai',
                    'country_code' => 'IN', 'currency_code' => 'INR', 'processor_slug' => 'razorpay', 'type' => 'apartment',
                    'occupancy_mode' => 'single', 'bedrooms' => 3, 'postal_code' => '400050',
                ],
                'tenant' => ['first_name' => 'Priya', 'last_name' => 'Sharma', 'email' => 'priya@example.com'],
                'lease'  => ['rent' => 7500000, 'due_day' => 5, 'start' => '-14 months'],
                'payments' => [['m' => 0, 's' => 'pending'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success'], ['m' => 3, 's' => 'success'], ['m' => 4, 's' => 'success'], ['m' => 5, 's' => 'success']],
                'fx' => 12000,
                'maintenance' => ['electrical', 'Power socket not working', 'Socket near window stopped working.', 'submitted'],
            ],
            [
                'property' => [
                    'name' => 'Shoreditch Studio', 'address_line1' => '8 Curtain Road', 'city' => 'London',
                    'country_code' => 'GB', 'currency_code' => 'GBP', 'processor_slug' => 'stripe', 'type' => 'apartment',
                    'occupancy_mode' => 'single', 'bedrooms' => 1, 'postal_code' => 'EC2A 3NH',
                ],
                'tenant' => ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'james@example.com'],
                'lease'  => ['rent' => 220000, 'due_day' => 15, 'start' => '-6 months'],
                'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success'], ['m' => 3, 's' => 'success'], ['m' => 4, 's' => 'success']],
                'fx' => 1260000,
                'maintenance' => null,
            ],
            [
                'property' => [
                    'name' => 'Lekki Phase 1', 'address_line1' => '21 Admiralty Way', 'city' => 'Lagos',
                    'country_code' => 'NG', 'currency_code' => 'NGN', 'processor_slug' => 'flutterwave', 'type' => 'house',
                    'occupancy_mode' => 'single', 'bedrooms' => 4, 'postal_code' => '101233',
                ],
                'tenant' => ['first_name' => 'Emeka', 'last_name' => 'Obi', 'email' => 'emeka@example.com'],
                'lease'  => ['rent' => 60000000, 'due_day' => 1, 'start' => '-3 months'],
                'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success']],
                'fx' => 650,
                'maintenance' => null,
            ],
            [
                'property' => [
                    'name' => 'Kemang Villa', 'address_line1' => 'Jl. Kemang Raya No. 5', 'city' => 'Jakarta',
                    'country_code' => 'ID', 'currency_code' => 'IDR', 'processor_slug' => 'xendit', 'type' => 'house',
                    'occupancy_mode' => 'single', 'bedrooms' => 3, 'postal_code' => '12730',
                ],
                'tenant' => ['first_name' => 'Budi', 'last_name' => 'Santoso', 'email' => 'budi@example.com'],
                'lease'  => ['rent' => 2500000000, 'due_day' => 1, 'start' => '-5 months', 'late_fee' => 50000000],
                'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success'], ['m' => 3, 's' => 'success'], ['m' => 4, 's' => 'success']],
                'fx' => 65,
                'maintenance' => null,
            ],
            [
                'property' => [
                    'name'               => 'Harbour View Apartments',
                    'address_line1'      => '100 Marina Boulevard',
                    'city'               => 'Singapore',
                    'country_code'       => 'SG',
                    'currency_code'      => 'SGD',
                    'processor_slug'     => 'stripe',
                    'type'               => 'apartment',
                    'occupancy_mode'     => 'multi',
                    'unit_capacity'      => 12,
                    'bedrooms'           => null,
                    'postal_code'        => '018987',
                ],
                'units' => [
                    [
                        'tenant'   => ['first_name' => 'Maya', 'last_name' => 'Lin', 'email' => 'maya.265@example.com'],
                        'lease'    => ['seq' => 1, 'label' => '265', 'rent' => 420000, 'due_day' => 1, 'start' => '-10 months'],
                        'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success']],
                        'fx'       => 740000,
                    ],
                    [
                        'tenant'   => ['first_name' => 'Ravi', 'last_name' => 'Menon', 'email' => 'ravi.388@example.com'],
                        'lease'    => ['seq' => 2, 'label' => '388', 'rent' => 385000, 'due_day' => 5, 'start' => '-7 months'],
                        'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success']],
                        'fx'       => 740000,
                    ],
                    [
                        'tenant'   => ['first_name' => 'Elena', 'last_name' => 'Kovács', 'email' => 'elena.12a@example.com'],
                        'lease'    => ['seq' => 3, 'label' => '12A', 'rent' => 510000, 'due_day' => 15, 'start' => '-4 months'],
                        'payments' => [['m' => 0, 's' => 'success'], ['m' => 1, 's' => 'success'], ['m' => 2, 's' => 'success']],
                        'fx'       => 740000,
                    ],
                    [
                        'tenant'   => ['first_name' => 'Tom', 'last_name' => 'Nguyen', 'email' => 'tom.ph@example.com'],
                        'lease'    => ['seq' => 4, 'label' => 'PH-2', 'rent' => 890000, 'due_day' => 1, 'start' => '-2 months'],
                        'payments' => [['m' => 0, 's' => 'success']],
                        'fx'       => 740000,
                    ],
                ],
            ],
        ];

        if ($landlord->properties()->exists()) {
            $this->command?->warn('Demo portfolio skipped — demo@rentersmaxx.com already has properties. Use migrate:fresh --seed to reset, or db:seed --class=SomeSeeder for one feature.');
        } else {
        foreach ($portfolio as $item) {
            $property = $landlord->properties()->create($item['property']);

            if (! empty($item['units'])) {
                foreach ($item['units'] as $unit) {
                    $l = $unit['lease'];
                    $tenant = User::firstOrCreate(
                        ['email' => $unit['tenant']['email']],
                        array_merge($unit['tenant'], ['role' => 'tenant', 'password' => bcrypt('password')])
                    );
                    $lease = $property->leases()->create([
                        'tenant_id'           => $tenant->id,
                        'unit_seq'             => $l['seq'],
                        'unit_label'           => $l['label'],
                        'rent_minor_units'     => $l['rent'],
                        'currency_code'        => $property->currency_code,
                        'due_day'              => $l['due_day'],
                        'grace_period_days'    => 5,
                        'late_fee_minor_units' => isset($l['late_fee']) ? (int) $l['late_fee'] : null,
                        'start_date'           => now()->modify($l['start'])->startOfMonth(),
                        'status'               => 'active',
                        'activated_at'         => now()->modify($l['start'])->startOfMonth(),
                        'frequency'            => 'monthly',
                    ]);
                    $mandate = $lease->mandates()->create([
                        'processor_slug'       => $property->processor_slug,
                        'processor_mandate_id'   => 'mandate_' . Str::random(14),
                        'status'               => 'active',
                        'payment_method_type'  => config('countries.' . $property->country_code . '.method', 'bank'),
                        'authorised_at'        => now()->modify($l['start'])->startOfMonth(),
                    ]);
                    foreach ($unit['payments'] as $p) {
                        $due  = now()->subMonths($p['m'])->startOfMonth()->addDays($l['due_day'] - 1);
                        $home = (int) round($l['rent'] * ($unit['fx'] / 1000000));
                        $lease->payments()->create([
                            'mandate_id'             => $mandate->id,
                            'processor_ref'          => 'pay_' . Str::random(14),
                            'amount_minor_units'     => $l['rent'],
                            'currency_code'          => $property->currency_code,
                            'fx_rate_snapshot'       => $unit['fx'],
                            'home_currency_code'     => 'USD',
                            'home_amount_minor_units'=> $home,
                            'status'                 => $p['s'],
                            'due_date'               => $due,
                            'collected_at'           => $p['s'] === 'success' ? $due->copy()->addDay() : null,
                            'retry_count'            => 0,
                        ]);
                    }
                }
                continue;
            }

            $tenant = User::firstOrCreate(
                ['email' => $item['tenant']['email']],
                array_merge($item['tenant'], ['role' => 'tenant', 'password' => bcrypt('password')])
            );
            $lease = $property->leases()->create([
                'tenant_id'           => $tenant->id,
                'unit_seq'             => 0,
                'unit_label'           => null,
                'rent_minor_units'     => $item['lease']['rent'],
                'currency_code'        => $property->currency_code,
                'due_day'              => $item['lease']['due_day'],
                'grace_period_days'    => 5,
                'late_fee_minor_units' => $item['lease']['late_fee'] ?? null,
                'start_date'           => now()->modify($item['lease']['start'])->startOfMonth(),
                'status'               => 'active',
                'activated_at'         => now()->modify($item['lease']['start'])->startOfMonth(),
                'frequency'            => 'monthly',
            ]);
            if (($property->occupancy_mode ?? 'single') === 'single') {
                $property->syncRentScheduleFromActiveLeases();
            }
            $mandate = $lease->mandates()->create([
                'processor_slug'       => $property->processor_slug,
                'processor_mandate_id' => 'mandate_' . Str::random(14),
                'status'               => 'active',
                'payment_method_type'  => config('countries.' . $property->country_code . '.method', 'bank'),
                'authorised_at'        => now()->modify($item['lease']['start'])->startOfMonth(),
            ]);
            foreach ($item['payments'] as $p) {
                $due  = now()->subMonths($p['m'])->startOfMonth()->addDays($item['lease']['due_day'] - 1);
                $home = (int) round($item['lease']['rent'] * ($item['fx'] / 1000000));
                $lease->payments()->create([
                    'mandate_id'              => $mandate->id,
                    'processor_ref'           => 'pay_' . Str::random(14),
                    'amount_minor_units'      => $item['lease']['rent'],
                    'currency_code'           => $property->currency_code,
                    'fx_rate_snapshot'        => $item['fx'],
                    'home_currency_code'      => 'USD',
                    'home_amount_minor_units' => $home,
                    'status'                  => $p['s'],
                    'due_date'                => $due,
                    'collected_at'            => $p['s'] === 'success' ? $due->copy()->addDay() : null,
                    'retry_count'             => $p['s'] === 'failed' ? 1 : 0,
                ]);
            }
            if ($item['maintenance']) {
                [$cat, $title, $desc, $status] = $item['maintenance'];
                $lease->maintenanceRequests()->create([
                    'raised_by'        => $tenant->id,
                    'category'         => $cat,
                    'title'            => $title,
                    'description'      => $desc,
                    'status'           => $status,
                    'acknowledged_at'  => $status === 'acknowledged' ? now()->subDays(2) : null,
                ]);
            }
        }
        }

        foreach ([['email' => 'w1@example.com', 'first_name' => 'David', 'last_name' => 'Chen'], ['email' => 'w2@example.com', 'first_name' => 'Anna', 'last_name' => 'Mueller']] as $w) {
            WaitlistEmail::firstOrCreate(['email' => $w['email']], array_merge($w, ['ref' => 'RMX-' . strtoupper(Str::random(6))]));
        }

        $this->call(MaintenanceTeamSeeder::class);
        $this->call(CleaningTeamSeeder::class);
        $this->call(PlatformSettingsSeeder::class);
        $this->call(TenantPaymentMethodSeeder::class);
        $this->call(TenantAccountLedgerSeeder::class);
        $this->call(SubLeaseSeeder::class);
        $this->call(LeaseTemplateSeeder::class);

        $this->command?->info('Seeded: demo@renpresso.com / password — properties, maintenance teams, cleaning crews');
    }
}
