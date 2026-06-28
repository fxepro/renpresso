<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'name', 'trigger_event', 'trigger_days', 'trigger_direction',
        'subject', 'body_html', 'available_variables',
        'is_published', 'landlord_can_edit', 'landlord_can_disable',
        'sort_order', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'available_variables'  => 'array',
            'is_published'         => 'boolean',
            'landlord_can_edit'    => 'boolean',
            'landlord_can_disable' => 'boolean',
            'trigger_days'         => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(LandlordEmailPreference::class);
    }

    public function sentLogs(): HasMany
    {
        return $this->hasMany(EmailSentLog::class);
    }

    /** Render subject/body replacing {{variable}} placeholders with real values. */
    public function render(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
            $template = str_replace('{{ '.$key.' }}', (string) $value, $template);
        }

        return $template;
    }

    public function triggerLabel(): string
    {
        return match ($this->trigger_event) {
            'rent_due_in_days'  => match (true) {
                $this->trigger_days === 0 => 'On rent due date',
                default => "{$this->trigger_days} days before due date",
            },
            'rent_overdue_days' => "{$this->trigger_days} day(s) overdue",
            'payment_success'   => 'Payment received',
            'payment_failed'    => 'Payment failed',
            'late_fee_applied'  => 'Late fee applied',
            'lease_expiry_days' => "{$this->trigger_days} days before lease expires",
            default => ucfirst(str_replace('_', ' ', $this->trigger_event)),
        };
    }

    /** All published templates, ordered for the scheduler. */
    public static function published()
    {
        return static::where('is_published', true)->orderBy('sort_order')->get();
    }
}
