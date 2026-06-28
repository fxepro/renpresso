<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    use HasUuids;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'payment_id', 'event_type', 'idempotency_key',
        'processor_payload', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['processor_payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function payment() { return $this->belongsTo(Payment::class); }
}
