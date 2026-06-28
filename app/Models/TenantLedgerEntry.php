<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLedgerEntry extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'lease_id', 'entry_date', 'description', 'paid_by',
        'charge_minor_units', 'payment_minor_units', 'category',
        'payment_id', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function formattedCharge(): ?string
    {
        return $this->charge_minor_units > 0
            ? number_format($this->charge_minor_units / 100, 2)
            : null;
    }

    public function formattedPayment(): ?string
    {
        return $this->payment_minor_units > 0
            ? number_format($this->payment_minor_units / 100, 2)
            : null;
    }
}
