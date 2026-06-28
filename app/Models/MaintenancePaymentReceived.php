<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePaymentReceived extends Model
{
    use HasUuids;

    protected $table = 'maintenance_payments_received';

    protected $fillable = [
        'maintenance_team_id',
        'maintenance_invoice_id',
        'landlord_id',
        'amount_minor',
        'currency_code',
        'paid_on',
        'method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'amount_minor' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTeam::class, 'maintenance_team_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInvoice::class, 'maintenance_invoice_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function formattedAmount(): string
    {
        $major = number_format($this->amount_minor / 100, 2);

        return $this->currency_code.' '.$major;
    }
}
