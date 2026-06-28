<?php
namespace App\Policies;
use App\Models\{User, Lease};
class LeasePolicy {
    public function view(User $user, Lease $lease): bool {
        return $user->id === $lease->property->landlord_id || $user->id === $lease->tenant_id;
    }
    public function update(User $user, Lease $lease): bool {
        return $user->id === $lease->property->landlord_id;
    }

    /** Landlord or tenant on the lease may post in the lease thread. */
    public function message(User $user, Lease $lease): bool
    {
        return $this->view($user, $lease);
    }
}
