<?php

namespace App\Models;

use App\Models\Concerns\HasPaymentMethodDisplay;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordPaymentMethod extends Model
{
    use HasPaymentMethodDisplay, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'method_type', 'label', 'brand', 'last4',
        'external_ref', 'is_default', 'status', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'meta'       => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
