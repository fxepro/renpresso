<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInvoiceLine extends Model
{
    use HasUuids;

    protected $fillable = [
        'maintenance_invoice_id',
        'sort_order',
        'description',
        'quantity',
        'unit_price_minor',
        'line_total_minor',
    ];

    protected function casts(): array
    {
        return [
            'quantity'         => 'decimal:3',
            'unit_price_minor' => 'integer',
            'line_total_minor' => 'integer',
            'sort_order'       => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInvoice::class, 'maintenance_invoice_id');
    }

    public static function computeLineTotalMinor(float|string $quantity, int $unitPriceMinor): int
    {
        return (int) round((float) $quantity * $unitPriceMinor);
    }
}
