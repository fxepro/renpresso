<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentMandate extends Model
{
    use HasUuids;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'lease_id', 'processor_slug', 'processor_mandate_id',
        'status', 'payment_method_type', 'authorised_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return ['authorised_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function lease()    { return $this->belongsTo(Lease::class); }
    public function payments() { return $this->hasMany(Payment::class, 'mandate_id'); }
}
