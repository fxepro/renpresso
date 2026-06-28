<?php

namespace App\Models;

use App\Models\Concerns\HasPaymentMethodDisplay;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPaymentMethod extends Model
{
    use HasPaymentMethodDisplay, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'lease_id', 'method_type', 'label', 'brand', 'last4',
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

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function hasCardBillingAddress(?User $user = null): bool
    {
        if ($this->method_type !== 'card') {
            return true;
        }

        if ($this->billingSameAsIdAddress()) {
            $user = $user ?? $this->user;

            return $user && filled($user->kyc_address_line1);
        }

        return filled(($this->meta ?? [])['billing_line1'] ?? null);
    }
}
