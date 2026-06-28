<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function view(User $user, MaintenanceRequest $mr): bool
    {
        if ($user->id === $mr->lease->property->landlord_id) {
            return true;
        }

        if ($user->id === $mr->lease->tenant_id) {
            return true;
        }

        return $this->maintenanceUserCanAccess($user, $mr);
    }

    /** Tenant or assigned maintenance team can add follow-up notes / photos. */
    public function followUp(User $user, MaintenanceRequest $mr): bool
    {
        if ($user->id === $mr->lease->tenant_id) {
            return true;
        }

        return $this->maintenanceUserCanAccess($user, $mr);
    }

    public function update(User $user, MaintenanceRequest $mr): bool
    {
        if ($user->id === $mr->lease->property->landlord_id) {
            return true;
        }

        return $this->maintenanceUserCanAccess($user, $mr);
    }

    public function assign(User $user, MaintenanceRequest $mr): bool
    {
        return $user->id === $mr->lease->property->landlord_id;
    }

    public function create(User $user): bool
    {
        if ($user->isTenant()) {
            return (bool) $user->primaryActiveLease();
        }

        return $user->isLandlord() && $user->properties()->whereHas('leases', fn ($q) => $q->where('status', 'active'))->exists();
    }

    /** Edit title, description, and photos (not status). */
    public function updateDetails(User $user, MaintenanceRequest $mr): bool
    {
        if ($user->id === $mr->lease->property->landlord_id) {
            return true;
        }

        return $user->id === $mr->raised_by
            && $user->id === $mr->lease->tenant_id
            && $mr->status === 'submitted';
    }

    public function delete(User $user, MaintenanceRequest $mr): bool
    {
        return $user->id === $mr->raised_by
            && $user->id === $mr->lease->tenant_id
            && $mr->status === 'submitted';
    }

    private function maintenanceUserCanAccess(User $user, MaintenanceRequest $mr): bool
    {
        if (! $user->isMaintenance()) {
            return false;
        }

        if ($mr->assignee_id === $user->id) {
            return true;
        }

        return $mr->maintenance_team_id
            && $user->ownedMaintenanceTeam
            && $mr->maintenance_team_id === $user->ownedMaintenanceTeam->id;
    }
}
