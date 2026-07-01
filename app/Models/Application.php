<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'property_id', 'target_unit_label', 'first_name', 'last_name', 'email', 'phone',
        'move_in_date', 'monthly_income_minor_units', 'income_currency',
        'message', 'status', 'landlord_notes', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'reviewed_at'  => 'datetime',
        ];
    }

    public function property()         { return $this->belongsTo(Property::class); }
    public function backgroundChecks() { return $this->hasMany(BackgroundCheck::class); }

    public function fullName(): string { return "{$this->first_name} {$this->last_name}"; }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'   => 'gold',
            'reviewing' => 'navy',
            'approved'  => 'green',
            'rejected'  => 'red',
            default     => 'grey',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    public function formattedIncome(): string
    {
        if (!$this->monthly_income_minor_units) return '—';
        return number_format($this->monthly_income_minor_units /100,2) . ' ' . ($this->income_currency ?? '');
    }
}
