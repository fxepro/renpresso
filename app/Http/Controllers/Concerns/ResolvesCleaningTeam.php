<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CleaningTeam;
use Illuminate\Support\Facades\Auth;

trait ResolvesCleaningTeam
{
    protected function cleaningTeam(): ?CleaningTeam
    {
        return Auth::user()?->ownedCleaningTeam;
    }

    protected function cleaningTeamOrAbort(): CleaningTeam
    {
        $team = $this->cleaningTeam();
        abort_unless($team, 403, 'Set up your cleaning crew profile first.');

        return $team;
    }
}
