<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTeamCity extends Model
{
    use HasUuids;

    protected $fillable = [
        'maintenance_team_id',
        'city',
        'country_code',
        'region',
        'is_primary',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTeam::class, 'maintenance_team_id');
    }

    public function label(): string
    {
        $parts = array_filter([$this->city, $this->region, strtoupper($this->country_code)]);

        return implode(', ', $parts);
    }
}
