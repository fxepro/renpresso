<?php

namespace App\Http\Controllers\Concerns;

use App\Models\MaintenanceTeam;
use Illuminate\Support\Facades\Auth;

trait ResolvesMaintenanceTeam
{
    protected function maintenanceTeam(): ?MaintenanceTeam
    {
        return Auth::user()?->ownedMaintenanceTeam;
    }

    protected function maintenanceTeamOrAbort(): MaintenanceTeam
    {
        $team = $this->maintenanceTeam();
        abort_unless($team, 403, 'Set up your maintenance team profile first.');

        return $team;
    }
}
