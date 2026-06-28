<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RepatriationLog extends Model
{
    use HasUuids;

    protected $keyType  = 'string';
    public    $incrementing = false;
    protected $table = 'repatriation_logs';

    protected $fillable = [
        'landlord_id', 'property_id',
        'amount_minor_units', 'currency_code',
        'fx_rate_snapshot', 'home_currency_code', 'home_amount_minor_units',
        'repatriated_on', 'notes',
    ];

    protected function casts(): array
    {
        return ['repatriated_on' => 'date'];
    }

    public function landlord() { return $this->belongsTo(User::class, 'landlord_id'); }
    public function property() { return $this->belongsTo(Property::class); }
}
