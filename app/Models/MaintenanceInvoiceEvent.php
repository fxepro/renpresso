<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInvoiceEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'maintenance_invoice_id',
        'actor_user_id',
        'event',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInvoice::class, 'maintenance_invoice_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function label(): string
    {
        return match ($this->event) {
            'created'           => 'Invoice created',
            'updated'           => 'Invoice updated',
            'sent'              => 'Invoice sent',
            'status_changed'    => 'Status changed',
            'attachment_added'  => 'Attachment added',
            'attachment_removed'=> 'Attachment removed',
            'payment_linked'      => 'Payment recorded',
            'landlord_approved'   => 'Approved by landlord',
            'payment_processed'   => 'Payment processed',
            default               => ucfirst(str_replace('_', ' ', $this->event)),
        };
    }
}
