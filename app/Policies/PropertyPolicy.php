<?php
namespace App\Policies;
use App\Models\{User, Property};
class PropertyPolicy {
    public function view(User $user, Property $property): bool {
        return $user->id === $property->landlord_id;
    }
    public function update(User $user, Property $property): bool {
        return $user->id === $property->landlord_id;
    }

    /** Property-wide notice to all tenants (email/SMS blast), not per-lease. */
    public function broadcast(User $user, Property $property): bool
    {
        return $user->id === $property->landlord_id;
    }
}
