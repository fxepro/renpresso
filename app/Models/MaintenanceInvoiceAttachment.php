<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInvoiceAttachment extends Model
{
    use HasUuids;

    public const KINDS = ['invoice_pdf', 'photo', 'receipt', 'other'];

    protected $fillable = [
        'maintenance_invoice_id',
        'uploaded_by',
        'kind',
        'file_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'caption',
    ];

    protected $hidden = ['file_path'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInvoice::class, 'maintenance_invoice_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function kindLabel(): string
    {
        return match ($this->kind) {
            'invoice_pdf' => 'Invoice PDF',
            'photo'       => 'Photo',
            'receipt'     => 'Receipt',
            default       => 'Other',
        };
    }
}
