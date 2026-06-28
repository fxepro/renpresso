<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'landlord')
            ->update([
                'landlord_account_status'        => 'pending_activation',
                'portfolio_committed_units'      => null,
                'portfolio_activation_fee_minor' => null,
                'portfolio_activation_units'     => null,
                'portfolio_activation_paid_at'   => null,
            ]);
    }

    public function down(): void
    {
        // No-op — activation state is environment-specific.
    }
};
