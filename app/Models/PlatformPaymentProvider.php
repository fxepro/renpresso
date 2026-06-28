<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PlatformPaymentProvider extends Model
{
    use HasUuids;

    /** Legacy DB column; registry is one row per payment company. */
    public const DEFAULT_CATEGORY = 'processor';

    protected $fillable = [
        'slug',
        'name',
        'category',
        'is_enabled',
        'is_configured',
        'env_keys',
        'setup_notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'     => 'boolean',
            'is_configured'  => 'boolean',
            'env_keys'       => 'array',
            'sort_order'     => 'integer',
        ];
    }

    /** Upsert providers from config; remove slugs no longer in registry. */
    public static function syncFromConfig(): void
    {
        $slugs = [];

        foreach (config('platform_payment_providers.providers', []) as $row) {
            $slugs[] = $row['slug'];

            $provider = static::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name'        => $row['name'],
                    'category'    => $row['role'] ?? $row['category'] ?? self::DEFAULT_CATEGORY,
                    'sort_order'  => $row['sort_order'] ?? 0,
                    'env_keys'    => $row['env_keys'] ?? null,
                    'setup_notes' => $row['setup_notes'] ?? null,
                ]
            );

            $provider->refreshConfiguredFromEnv();

            if (! empty($row['auto_enable_when_configured']) && $provider->is_configured) {
                $provider->update(['is_enabled' => true]);
            }
        }

        if ($slugs !== []) {
            static::query()->whereNotIn('slug', $slugs)->delete();
        }
    }

    protected static function registryDefinition(string $slug): ?array
    {
        static $bySlug = null;

        if ($bySlug === null) {
            $bySlug = collect(config('platform_payment_providers.providers', []))
                ->keyBy('slug')
                ->all();
        }

        return $bySlug[$slug] ?? null;
    }

    public function refreshConfiguredFromEnv(): void
    {
        $definition = self::registryDefinition($this->slug);
        $configured = false;

        if ($definition && ! empty($definition['env_vars'])) {
            $configured = collect($definition['env_vars'])->every(fn (string $var) => filled(env($var)));
        } elseif (! empty($definition['service']) && ! empty($this->env_keys)) {
            $block = config('services.'.$definition['service'], []);
            $configured = collect($this->env_keys)->every(fn (string $key) => filled($block[$key] ?? null));
        } elseif ($definition) {
            $block = config('services.'.$this->slug, []);
            $configured = is_array($block) && collect($block)->filter(fn ($v) => filled($v))->isNotEmpty();
        }

        $this->forceFill(['is_configured' => $configured])->saveQuietly();
    }

    public function envHint(): string
    {
        $definition = self::registryDefinition($this->slug);

        if (! empty($definition['env_vars'])) {
            return implode(', ', $definition['env_vars']);
        }

        if (! empty($definition['service']) && ! empty($this->env_keys)) {
            $service = strtoupper($definition['service']);

            return collect($this->env_keys)
                ->map(fn (string $key) => $service.'_* → '.$key)
                ->implode(', ');
        }

        return '—';
    }
}
