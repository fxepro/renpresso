<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubLease extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'parent_lease_id', 'subletter_id', 'created_by',
        'rent_minor_units', 'currency_code', 'due_day', 'grace_period_days', 'frequency',
        'start_date', 'end_date', 'status', 'landlord_approved_at', 'landlord_rejection_reason', 'label',
    ];

    protected function casts(): array
    {
        return [
            'start_date'            => 'date',
            'end_date'              => 'date',
            'landlord_approved_at'  => 'datetime',
        ];
    }

    public function parentLease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'parent_lease_id');
    }

    public function subletter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subletter_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function formattedRent(): string
    {
        return number_format($this->rent_minor_units /100,2).' '.$this->currency_code;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
