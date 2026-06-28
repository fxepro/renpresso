<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'property_id', 'tenant_id', 'unit_seq', 'unit_label', 'rent_minor_units', 'currency_code',
        'due_day', 'grace_period_days', 'late_fee_minor_units', 'frequency', 'deposit_minor_units',
        'start_date', 'end_date', 'status', 'use_business_entity', 'ledger_starting_balance_minor_units',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'activated_at' => 'datetime',
            'unit_seq'             => 'integer',
            'use_business_entity'  => 'boolean',
        ];
    }

    public function resolvesUseBusinessEntity(): bool
    {
        if ($this->use_business_entity !== null) {
            return (bool) $this->use_business_entity;
        }

        return $this->property?->landlord?->shouldUseBusinessEntityInLease() ?? false;
    }

    public function leaseLandlordName(): string
    {
        return $this->property->landlord->leasePartyName($this->resolvesUseBusinessEntity());
    }

    public function leaseLandlordAddress(): string
    {
        return $this->property->landlord->leasePartyAddress($this->resolvesUseBusinessEntity());
    }

    public function leaseLandlordEin(): string
    {
        if (! $this->resolvesUseBusinessEntity()) {
            return '—';
        }

        $ein = trim((string) ($this->property->landlord->business_ein ?? ''));

        return $ein !== '' ? $ein : '—';
    }

    public function property()           { return $this->belongsTo(Property::class); }
    public function tenant()             { return $this->belongsTo(User::class, 'tenant_id'); }
    public function mandates()           { return $this->hasMany(PaymentMandate::class); }
    public function payments()           { return $this->hasMany(Payment::class); }
    public function ledgerEntries()      { return $this->hasMany(TenantLedgerEntry::class); }
    public function subLeases()          { return $this->hasMany(SubLease::class, 'parent_lease_id'); }

    public function activeSubLeases()
    {
        return $this->subLeases()->where('status', 'active')->with('subletter');
    }
    public function maintenanceRequests(){ return $this->hasMany(MaintenanceRequest::class); }
    public function messages()           { return $this->hasMany(Message::class); }
    public function documents()          { return $this->morphMany(Document::class, 'documentable'); }

    public function activeMandate()
    {
        return $this->mandates()->where('status', 'active')->latest()->first();
    }

    /** Rent formatted for display */
    public function formattedRent(): string
    {
        return number_format($this->rent_minor_units / 100, 2) . ' ' . $this->currency_code;
    }

    /** Next calendar due date from lease due_day (1–28). */
    public function nextRentDueDate(): \Carbon\Carbon
    {
        $day = min(max((int) $this->due_day, 1), 28);
        $due = now()->copy()->day($day)->startOfDay();
        if ($due->lte(now()->startOfDay())) {
            $due->addMonthNoOverflow()->day(min($day, $due->daysInMonth));
        }

        return $due;
    }

    /** Single-unit whole-building leases use internal #0 — show as em dash in UI. */
    public function displayUnitSeq(): string
    {
        if ($this->property && ! $this->property->isMultiUnit()) {
            return '—';
        }

        return (int) $this->unit_seq > 0 ? (string) (int) $this->unit_seq : '—';
    }

    /** Door / apt label; empty or literal "0" (legacy single-unit) → em dash. */
    public function displayUnitLabel(): string
    {
        $label = trim((string) ($this->unit_label ?? ''));

        return ($label === '' || $label === '0') ? '—' : $label;
    }

    public static function ordinalDay(int $day): string
    {
        $day = min(max($day, 1), 28);

        return match ($day) {
            1       => '1st',
            2       => '2nd',
            3       => '3rd',
            default => $day.'th',
        };
    }

    /** Calendar day of month when late fees apply (due + grace days, capped at 28). */
    public function lateFeeDayOfMonth(): int
    {
        $due   = min(max((int) $this->due_day, 1), 28);
        $grace = max(0, (int) $this->grace_period_days);

        return min(28, $due + $grace);
    }

    public function formattedLateFee(): ?string
    {
        if (! $this->late_fee_minor_units) {
            return null;
        }

        return number_format($this->late_fee_minor_units /100,2).' '.$this->currency_code;
    }

    public function formattedPaymentSchedule(): string
    {
        $due   = self::ordinalDay((int) $this->due_day);
        $grace = (int) $this->grace_period_days;
        $late  = self::ordinalDay($this->lateFeeDayOfMonth());
        $fee   = $this->formattedLateFee();

        $line = "Rent due {$due} · {$grace}-day grace · Late fee from {$late}";

        return $fee ? "{$line} ({$fee})" : $line;
    }

    /** Landlord-facing label; internal slot is {@see $unit_seq}. */
    public function displayUnit(): string
    {
        if ($this->property && ! $this->property->isMultiUnit()) {
            return $this->displayUnitLabel();
        }

        $seq = $this->displayUnitSeq();

        return $seq === '—' ? '—' : '#'.$seq.' · '.$this->displayUnitLabel();
    }
}
