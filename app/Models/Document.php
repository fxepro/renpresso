<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'documentable_type', 'documentable_id',
        'uploaded_by', 'type', 'disk', 'path',
        'original_filename', 'mime_type', 'size_bytes', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function documentable() { return $this->morphTo(); }
    public function uploadedBy()   { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function formattedSize(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        return round($bytes / 1024, 1) . ' KB';
    }

    /** Files tied to properties, leases, or maintenance — not listing photos (Spatie media). */
    public function scopeAccessibleForUser(Builder $query, User $user): Builder
    {
        $maintenanceRequestIds = MaintenanceRequest::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('lease.property', fn ($p) => $p->where('landlord_id', $user->id))
                    ->orWhereHas('lease', fn ($l) => $l->where('tenant_id', $user->id));

                if ($user->isMaintenance()) {
                    $q->orWhere('assignee_id', $user->id);
                    $teamId = $user->ownedMaintenanceTeam?->id;
                    if ($teamId) {
                        $q->orWhere('maintenance_team_id', $teamId);
                    }
                }
            })
            ->select('id');

        return $query->where(function (Builder $outer) use ($user, $maintenanceRequestIds) {
            $outer->where(function (Builder $q) use ($user) {
                $q->where('documentable_type', Property::class)
                    ->whereIn('documentable_id', Property::query()->where('landlord_id', $user->id)->select('id'));
            });

            $outer->orWhere(function (Builder $q) use ($user) {
                $q->where('documentable_type', Lease::class)
                    ->whereIn(
                        'documentable_id',
                        Lease::query()
                            ->where(function (Builder $l) use ($user) {
                                $l->where('tenant_id', $user->id)
                                    ->orWhereHas('property', fn ($p) => $p->where('landlord_id', $user->id));
                            })
                            ->select('id')
                    );
            });

            $outer->orWhere(function (Builder $q) use ($maintenanceRequestIds) {
                $q->where('documentable_type', MaintenanceRequest::class)
                    ->whereIn('documentable_id', $maintenanceRequestIds);
            });

            $outer->orWhere(function (Builder $q) use ($maintenanceRequestIds) {
                $q->where('documentable_type', MaintenanceRequestUpdate::class)
                    ->whereIn(
                        'documentable_id',
                        MaintenanceRequestUpdate::query()
                            ->whereIn('maintenance_request_id', $maintenanceRequestIds)
                            ->select('id')
                    );
            });
        });
    }

    public function linkedEntityLabel(): string
    {
        $entity = $this->documentable;

        return match (true) {
            $entity instanceof Property => 'Property · '.$entity->name,
            $entity instanceof Lease => 'Lease · '.($entity->property?->name ?? '—'),
            $entity instanceof MaintenanceRequest => 'Maintenance · '.($entity->title ?? '—'),
            $entity instanceof MaintenanceRequestUpdate => 'Maintenance follow-up · '.(
                $entity->loadMissing('maintenanceRequest')->maintenanceRequest?->title ?? '—'
            ),
            default => str_replace('App\Models\\', '', (string) $this->documentable_type),
        };
    }

    /** Folder-style group for filters: property | lease | maintenance | followup | other */
    public function locationKey(): string
    {
        return match ($this->documentable_type) {
            Property::class => 'property',
            Lease::class => 'lease',
            MaintenanceRequest::class => 'maintenance',
            MaintenanceRequestUpdate::class => 'followup',
            default => 'other',
        };
    }

    public function locationLabel(): string
    {
        return match ($this->locationKey()) {
            'property' => 'Property',
            'lease' => 'Lease',
            'maintenance' => 'Maintenance',
            'followup' => 'Follow-up',
            default => 'Other',
        };
    }
}
