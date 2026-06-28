<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordPayoutAccount extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'country_code', 'currency_code', 'purpose', 'label', 'holder_name', 'bank_name',
        'iban', 'local_account_ref', 'local_routing_ref', 'display_hint', 'status',
    ];

    public const PURPOSE_COLLECTION   = 'collection';
    public const PURPOSE_REPATRIATION = 'repatriation';

    protected function casts(): array
    {
        return [
            'iban'               => 'encrypted',
            'local_account_ref'  => 'encrypted',
            'local_routing_ref'  => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
