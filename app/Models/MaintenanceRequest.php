<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'lease_id', 'raised_by', 'assignee_id', 'maintenance_team_id', 'assigned_at', 'category', 'title', 'description',
        'status', 'resolution_notes', 'acknowledged_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
            'resolved_at'     => 'datetime',
            'assigned_at'     => 'datetime',
        ];
    }

    public function lease()     { return $this->belongsTo(Lease::class); }
    public function raisedBy()  { return $this->belongsTo(User::class, 'raised_by'); }
    public function assignee()  { return $this->belongsTo(User::class, 'assignee_id'); }
    public function maintenanceTeam() { return $this->belongsTo(MaintenanceTeam::class); }
    public function documents() { return $this->morphMany(Document::class, 'documentable'); }

    /** Tenant/staff follow-ups (text + photos on each update). */
    public function followUps(): HasMany
    {
        return $this->hasMany(MaintenanceRequestUpdate::class)->orderBy('created_at');
    }
}
