<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'lease_id', 'mandate_id', 'processor_ref',
        'amount_minor_units', 'currency_code',
        'fx_rate_snapshot', 'home_currency_code', 'home_amount_minor_units',
        'status', 'retry_count', 'due_date', 'collected_at',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'datetime', 'collected_at' => 'datetime'];
    }

    public function lease()   { return $this->belongsTo(Lease::class); }
    public function mandate() { return $this->belongsTo(PaymentMandate::class, 'mandate_id'); }
    public function events()  { return $this->hasMany(PaymentEvent::class); }

    /** FX rate as a decimal (stored as integer × 1,000,000) */
    public function fxRate(): float
    {
        return $this->fx_rate_snapshot / 1_000_000;
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount_minor_units / 100, 2) . ' ' . $this->currency_code;
    }

    public function ledgerMethodLabel(): string
    {
        $type = $this->mandate?->payment_method_type;

        if (! $type) {
            return '—';
        }

        return ucfirst(str_replace('_', ' ', $type));
    }
}
