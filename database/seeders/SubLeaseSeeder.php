<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Property;
use App\Models\SubLease;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubLeaseSeeder extends Seeder
{
    public function run(): void
    {
        $kemang = Property::query()->where('name', 'Kemang Villa')->first();
        if ($kemang) {
            $kemang->update([
                'sublet_allowed'                    => true,
                'sublet_bg_check_required'          => true,
                'sublet_landlord_approval_required' => true,
            ]);
        }

        $shoreditch = Property::query()->where('name', 'Shoreditch Studio')->first();
        if ($shoreditch) {
            $shoreditch->update([
                'sublet_allowed'                    => true,
                'sublet_bg_check_required'          => true,
                'sublet_landlord_approval_required' => false,
            ]);
        }

        $lease = Lease::query()
            ->where('status', 'active')
            ->whereHas('property', fn ($q) => $q->where('name', 'Kemang Villa'))
            ->with('tenant')
            ->first();

        if (! $lease || ! $lease->tenant) {
            return;
        }

        SubLease::query()->where('parent_lease_id', $lease->id)->delete();

        $subletter = User::firstOrCreate(
            ['email' => 'sari.kemang@example.com'],
            [
                'first_name' => 'Sari',
                'last_name'  => 'Wijaya',
                'password'   => bcrypt('password'),
                'role'       => 'tenant',
            ]
        );

        SubLease::create([
            'parent_lease_id'      => $lease->id,
            'subletter_id'         => $subletter->id,
            'created_by'           => $lease->tenant_id,
            'rent_minor_units'     => (int) round($lease->rent_minor_units * 0.35),
            'currency_code'        => $lease->currency_code,
            'due_day'              => $lease->due_day,
            'grace_period_days'    => $lease->grace_period_days,
            'frequency'            => $lease->frequency,
            'start_date'           => $lease->start_date,
            'end_date'             => $lease->end_date,
            'status'               => 'active',
            'landlord_approved_at' => now()->subMonths(2),
            'label'                => 'Bedroom 2',
        ]);
    }
}
