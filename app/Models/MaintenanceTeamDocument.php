<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTeamDocument extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'maintenance_team_id',
        'document_type',
        'file_path',
        'status',
        'reference_number',
        'expires_on',
        'submitted_at',
        'verified_at',
        'rejection_reason',
    ];

    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return [
            'expires_on'   => 'date',
            'submitted_at' => 'datetime',
            'verified_at'  => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTeam::class, 'maintenance_team_id');
    }

    public function isEditable(): bool
    {
        return ! in_array($this->status, ['pending', 'verified'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'verified' => 'Verified',
            'pending'  => 'Under review',
            'rejected' => 'Rejected',
            default    => 'Not submitted',
        };
    }
}
