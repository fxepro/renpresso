<?php

namespace Database\Seeders;

use App\Models\LeaseTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaseTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::query()->where('email', 'demo@rentersmaxx.com')->first();
        if (! $landlord) {
            return;
        }

        $masterBodyPath = base_path('docs/RESIDENTIAL LEASE AGREEMENT');
        $masterBody = is_readable($masterBodyPath)
            ? trim((string) file_get_contents($masterBodyPath))
            : null;

        LeaseTemplate::query()->updateOrCreate(
            [
                'landlord_id' => $landlord->id,
                'name'        => 'Residential lease agreement (master)',
            ],
            [
                'lease_type'  => 'master',
                'description' => 'Standard residential master lease with rent breakdown (base, trash, water/sewer, total). Source: docs/RESIDENTIAL LEASE AGREEMENT',
                'body'        => $masterBody ?: "See docs/RESIDENTIAL LEASE AGREEMENT",
            ]
        );

        LeaseTemplate::query()->firstOrCreate(
            ['landlord_id' => $landlord->id, 'name' => 'Sub-lease agreement'],
            [
                'lease_type'  => 'sub_lease',
                'description' => 'Sub-lessee agreement inheriting master lease terms.',
                'body'        => 'Sub-rent and dates set by primary tenant. Landlord approval when required on property.',
            ]
        );
    }
}
