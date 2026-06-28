<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceInvoice extends Model
{
    use HasUuids;

    public const STATUSES = ['draft', 'sent', 'partially_paid', 'paid', 'cancelled'];

    protected $fillable = [
        'maintenance_team_id',
        'landlord_id',
        'maintenance_request_id',
        'property_id',
        'invoice_number',
        'amount_minor',
        'subtotal_minor',
        'tax_minor',
        'currency_code',
        'status',
        'due_date',
        'description',
        'bill_to_name',
        'bill_to_email',
        'notes_customer',
        'notes_internal',
        'issued_at',
        'sent_at',
        'paid_at',
        'cancelled_at',
        'landlord_approved_at',
        'landlord_approved_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date'       => 'date',
            'issued_at'      => 'datetime',
            'sent_at'        => 'datetime',
            'paid_at'        => 'datetime',
            'cancelled_at'         => 'datetime',
            'landlord_approved_at' => 'datetime',
            'amount_minor'         => 'integer',
            'subtotal_minor' => 'integer',
            'tax_minor'      => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTeam::class, 'maintenance_team_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function landlordApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_approved_by');
    }

    public function scopeVisibleToLandlord(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['draft']);
    }

    public function scopeAwaitingLandlordApproval(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'partially_paid'])
            ->whereNull('landlord_approved_at');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(MaintenanceInvoiceLine::class, 'maintenance_invoice_id')->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceInvoiceAttachment::class, 'maintenance_invoice_id')->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(MaintenanceInvoiceEvent::class, 'maintenance_invoice_id')->latest('created_at');
    }

    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(MaintenancePaymentReceived::class, 'maintenance_invoice_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function amountPaidMinor(): int
    {
        return (int) $this->paymentsReceived()->sum('amount_minor');
    }

    public function amountDueMinor(): int
    {
        return max(0, (int) $this->amount_minor - $this->amountPaidMinor());
    }

    public function formattedAmount(): string
    {
        return $this->formatMinor($this->amount_minor);
    }

    public function formattedAmountDue(): string
    {
        return $this->formatMinor($this->amountDueMinor());
    }

    public function formatMinor(int $minor): string
    {
        return $this->currency_code.' '.number_format($minor / 100, 2);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'partially_paid' => 'Partially paid',
            default          => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusBadgeClass(): string
    {
        if ($this->needsLandlordApproval()) {
            return 'gold';
        }

        return match ($this->status) {
            'paid'           => 'green',
            'sent'           => 'gold',
            'partially_paid' => 'gold',
            'cancelled'      => 'grey',
            default          => 'grey',
        };
    }

    public function needsLandlordApproval(): bool
    {
        return $this->amountDueMinor() > 0
            && in_array($this->status, ['sent', 'partially_paid'], true)
            && $this->landlord_approved_at === null;
    }

    public function landlordStatusLabel(): string
    {
        if ($this->needsLandlordApproval()) {
            return 'Awaiting approval';
        }

        if ($this->landlord_approved_at && $this->status !== 'paid') {
            return 'Approved · '.$this->statusLabel();
        }

        return $this->statusLabel();
    }

    public function recalculateFromLines(): void
    {
        $subtotal = (int) $this->lines()->sum('line_total_minor');
        $tax = (int) ($this->tax_minor ?? 0);
        $this->forceFill([
            'subtotal_minor' => $subtotal,
            'amount_minor'   => $subtotal + $tax,
        ])->saveQuietly();
    }

    public function syncStatusFromPayments(): void
    {
        if ($this->isCancelled() || $this->isDraft()) {
            return;
        }

        $paid = $this->amountPaidMinor();
        $total = (int) $this->amount_minor;

        if ($total > 0 && $paid >= $total) {
            $this->forceFill([
                'status'  => 'paid',
                'paid_at' => $this->paid_at ?? now(),
            ])->saveQuietly();

            return;
        }

        if ($paid > 0 && $paid < $total) {
            $this->forceFill([
                'status'  => 'partially_paid',
                'paid_at' => null,
            ])->saveQuietly();

            return;
        }

        if (in_array($this->status, ['paid', 'partially_paid'], true) && $paid < $total) {
            $this->forceFill([
                'status'  => $paid > 0 ? 'partially_paid' : 'sent',
                'paid_at' => null,
            ])->saveQuietly();
        }
    }

    public function recordEvent(string $event, ?array $payload = null, ?User $actor = null): MaintenanceInvoiceEvent
    {
        return $this->events()->create([
            'actor_user_id' => $actor?->id,
            'event'         => $event,
            'payload'       => $payload,
            'created_at'    => now(),
        ]);
    }

    public static function syncPaymentStatus(self $invoice): void
    {
        $invoice->refresh();
        $invoice->syncStatusFromPayments();
    }

    /** Landlord approves; platform records payment to the maintenance team. */
    public static function processLandlordApproval(self $invoice, User $landlord): void
    {
        $due = $invoice->amountDueMinor();

        $invoice->update([
            'landlord_approved_at' => now(),
            'landlord_approved_by'   => $landlord->id,
        ]);

        $invoice->recordEvent('landlord_approved', ['amount_minor' => $due], $landlord);

        $payment = $invoice->team->paymentsReceived()->create([
            'maintenance_invoice_id' => $invoice->id,
            'landlord_id'            => $landlord->id,
            'amount_minor'           => $due,
            'currency_code'          => $invoice->currency_code,
            'paid_on'                => now()->toDateString(),
            'method'                 => 'platform',
            'reference'              => 'SYS-'.now()->format('Ymd-His'),
            'notes'                  => 'Approved and paid via Renpresso',
        ]);

        self::syncPaymentStatus($invoice->fresh());
        $invoice->recordEvent('payment_processed', [
            'payment_id'   => $payment->id,
            'amount_minor' => $payment->amount_minor,
            'system'       => true,
        ], $landlord);
    }
}
