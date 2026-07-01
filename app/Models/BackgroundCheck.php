<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BackgroundCheck extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'application_id', 'property_id', 'type', 'method', 'status',
        'provider_ref', 'notes', 'document_path', 'document_name', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function application() { return $this->belongsTo(Application::class); }
    public function property()    { return $this->belongsTo(Property::class); }

    public function statusColor(): string
    {
        return match ($this->status) {
            'passed'        => 'green',
            'failed'        => 'red',
            'pending'       => 'gold',
            'manual_review' => 'navy',
            default         => 'grey',
        };
    }

    public function typeLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function methodLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->method));
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
