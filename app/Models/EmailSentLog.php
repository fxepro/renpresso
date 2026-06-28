<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSentLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'email_template_id', 'landlord_id', 'tenant_id', 'lease_id',
        'to_email', 'subject_sent', 'trigger_date', 'trigger_key',
        'status', 'error_message', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_date' => 'date',
            'sent_at'      => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }
}
