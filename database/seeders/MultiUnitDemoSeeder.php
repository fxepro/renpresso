<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Adds 3 extra multi-unit buildings for the demo landlord.
 * Safe to run multiple times — skips buildings that already exist by name.
 *
 * Usage:
 *   php artisan db:seed --class=MultiUnitDemoSeeder
 */
class MultiUnitDemoSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::where('email', 'demo@rentersmaxx.com')->firstOrFail();

        $buildings = [

            /* ── 1. London – Bloomsbury Court ── */
            [
                'property' => [
                    'name'           => 'Bloomsbury Court',
                    'address_line1'  => '14 Russell Square',
                    'city'           => 'London',
                    'postal_code'    => 'WC1B 5EA',
                    'country_code'   => 'GB',
                    'currency_code'  => 'GBP',
                    'processor_slug' => 'stripe',
                    'type'           => 'apartment',
                    'occupancy_mode' => 'multi',
                    'unit_capacity'  => 6,
                    'sublet_allowed' => false,
                    'rental_mode'    => 'long_term',
                    'listing_visibility' => 'public',
                ],
                'units' => [
                    [
                        'seq'      => 1, 'label' => '1A',
                        'bedrooms' => 1,
                        'tenant'   => ['first_name' => 'Clara',  'last_name' => 'Haynes',  'email' => 'clara.1a@example.com'],
                        'lease'    => ['rent' => 185000, 'due_day' => 1,  'start' => '-9 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'success'],['m'=>3,'s'=>'success'],['m'=>4,'s'=>'success']],
                        'fx'       => 1260000,
                    ],
                    [
                        'seq'      => 2, 'label' => '1B',
                        'bedrooms' => 2,
                        'tenant'   => ['first_name' => 'Marcus', 'last_name' => 'Bell',    'email' => 'marcus.1b@example.com'],
                        'lease'    => ['rent' => 240000, 'due_day' => 1,  'start' => '-5 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'failed'],['m'=>3,'s'=>'success']],
                        'fx'       => 1260000,
                    ],
                    // units 3-6 are vacant
                ],
            ],

            /* ── 2. Amsterdam – Jordaan Residences ── */
            [
                'property' => [
                    'name'           => 'Jordaan Residences',
                    'address_line1'  => 'Prinsengracht 112',
                    'city'           => 'Amsterdam',
                    'postal_code'    => '1015 EA',
                    'country_code'   => 'NL',
                    'currency_code'  => 'EUR',
                    'processor_slug' => 'stripe',
                    'type'           => 'apartment',
                    'occupancy_mode' => 'multi',
                    'unit_capacity'  => 8,
                    'sublet_allowed' => true,
                    'rental_mode'    => 'long_term',
                    'listing_visibility' => 'public',
                ],
                'units' => [
                    [
                        'seq'      => 1, 'label' => 'A1',
                        'bedrooms' => 0,
                        'tenant'   => ['first_name' => 'Lena',   'last_name' => 'Brandt',  'email' => 'lena.a1@example.com'],
                        'lease'    => ['rent' => 135000, 'due_day' => 5,  'start' => '-12 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'success'],['m'=>3,'s'=>'success'],['m'=>4,'s'=>'success'],['m'=>5,'s'=>'success']],
                        'fx'       => 1080000,
                    ],
                    [
                        'seq'      => 2, 'label' => 'A2',
                        'bedrooms' => 1,
                        'tenant'   => ['first_name' => 'Pieter', 'last_name' => 'de Vries', 'email' => 'pieter.a2@example.com'],
                        'lease'    => ['rent' => 165000, 'due_day' => 5,  'start' => '-6 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'success'],['m'=>3,'s'=>'pending']],
                        'fx'       => 1080000,
                    ],
                    [
                        'seq'      => 3, 'label' => 'B1',
                        'bedrooms' => 2,
                        'tenant'   => ['first_name' => 'Sofia',  'last_name' => 'Rios',    'email' => 'sofia.b1@example.com'],
                        'lease'    => ['rent' => 198000, 'due_day' => 1,  'start' => '-3 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success']],
                        'fx'       => 1080000,
                    ],
                    // units 4-8 vacant
                ],
            ],

            /* ── 3. Lagos – Victoria Island Towers ── */
            [
                'property' => [
                    'name'           => 'Victoria Island Towers',
                    'address_line1'  => '15 Adeola Odeku Street',
                    'city'           => 'Lagos',
                    'postal_code'    => '101233',
                    'country_code'   => 'NG',
                    'currency_code'  => 'NGN',
                    'processor_slug' => 'flutterwave',
                    'type'           => 'apartment',
                    'occupancy_mode' => 'multi',
                    'unit_capacity'  => 10,
                    'sublet_allowed' => false,
                    'rental_mode'    => 'long_term',
                    'listing_visibility' => 'private',
                ],
                'units' => [
                    [
                        'seq'      => 1, 'label' => '101',
                        'bedrooms' => 2,
                        'tenant'   => ['first_name' => 'Chidi',  'last_name' => 'Eze',     'email' => 'chidi.101@example.com'],
                        'lease'    => ['rent' => 45000000, 'due_day' => 1, 'start' => '-8 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'success'],['m'=>3,'s'=>'success']],
                        'fx'       => 650,
                    ],
                    [
                        'seq'      => 2, 'label' => '102',
                        'bedrooms' => 3,
                        'tenant'   => ['first_name' => 'Ngozi',  'last_name' => 'Adeyemi', 'email' => 'ngozi.102@example.com'],
                        'lease'    => ['rent' => 65000000, 'due_day' => 1, 'start' => '-4 months'],
                        'payments' => [['m'=>0,'s'=>'success'],['m'=>1,'s'=>'success'],['m'=>2,'s'=>'failed']],
                        'fx'       => 650,
                    ],
                    [
                        'seq'      => 3, 'label' => 'PH-1',
                        'bedrooms' => 4,
                        'tenant'   => ['first_name' => 'Tunde',  'last_name' => 'Bello',   'email' => 'tunde.ph1@example.com'],
                        'lease'    => ['rent' => 120000000, 'due_day' => 1, 'start' => '-2 months'],
                        'payments' => [['m'=>0,'s'=>'success']],
                        'fx'       => 650,
                    ],
                    // units 4-10 vacant
                ],
            ],
        ];

        foreach ($buildings as $item) {
            if ($landlord->properties()->where('name', $item['property']['name'])->exists()) {
                $this->command->warn("Skipping '{$item['property']['name']}' — already exists.");
                continue;
            }

            $property = $landlord->properties()->create($item['property']);
            $this->command->info("Created: {$property->name}");

            // seed unit_slots_meta with bedroom counts
            $slotsMeta = [];
            foreach ($item['units'] as $u) {
                $slotsMeta[$u['seq']] = ['bedrooms' => $u['bedrooms']];
            }
            $property->update(['unit_slots_meta' => $slotsMeta]);

            foreach ($item['units'] as $u) {
                $tenant = User::firstOrCreate(
                    ['email' => $u['tenant']['email']],
                    array_merge($u['tenant'], ['role' => 'tenant', 'password' => bcrypt('password')])
                );

                $lease = $property->leases()->create([
                    'tenant_id'           => $tenant->id,
                    'unit_seq'            => $u['seq'],
                    'unit_label'          => $u['label'],
                    'rent_minor_units'    => $u['lease']['rent'],
                    'currency_code'       => $property->currency_code,
                    'due_day'             => $u['lease']['due_day'],
                    'grace_period_days'   => 5,
                    'late_fee_minor_units'=> null,
                    'start_date'          => now()->modify($u['lease']['start'])->startOfMonth(),
                    'status'              => 'active',
                    'activated_at'        => now()->modify($u['lease']['start'])->startOfMonth(),
                    'frequency'           => 'monthly',
                ]);

                $mandate = $lease->mandates()->create([
                    'processor_slug'      => $property->processor_slug,
                    'processor_mandate_id'=> 'mandate_' . Str::random(14),
                    'status'              => 'active',
                    'payment_method_type' => config('countries.' . $property->country_code . '.method', 'bank'),
                    'authorised_at'       => now()->modify($u['lease']['start'])->startOfMonth(),
                ]);

                foreach ($u['payments'] as $p) {
                    $due  = now()->subMonths($p['m'])->startOfMonth()->addDays($u['lease']['due_day'] - 1);
                    $home = (int) round($u['lease']['rent'] * ($u['fx'] / 1_000_000));
                    $lease->payments()->create([
                        'mandate_id'              => $mandate->id,
                        'processor_ref'           => 'pay_' . Str::random(14),
                        'amount_minor_units'      => $u['lease']['rent'],
                        'currency_code'           => $property->currency_code,
                        'fx_rate_snapshot'        => $u['fx'],
                        'home_currency_code'      => 'USD',
                        'home_amount_minor_units' => $home,
                        'status'                  => $p['s'],
                        'due_date'                => $due,
                        'collected_at'            => $p['s'] === 'success' ? $due->copy()->addDay() : null,
                        'retry_count'             => $p['s'] === 'failed' ? 1 : 0,
                    ]);
                }
            }

            $property->syncStatusFromLeases();
        }

        $this->command->info('MultiUnitDemoSeeder done.');
    }
}
