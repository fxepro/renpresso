<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseTemplate extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'landlord_id',
        'name',
        'lease_type',
        'description',
        'body',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
    ];

    public const LEASE_TYPES = [
        'master'      => 'Master lease',
        'sub_lease'   => 'Sub-lease',
        'short_term'  => 'Short-term',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function hasFile(): bool
    {
        return $this->path !== null && $this->path !== '';
    }

    public function formattedSize(): ?string
    {
        if (! $this->size_bytes) {
            return null;
        }
        $bytes = (int) $this->size_bytes;
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1).' MB';
        }

        return round($bytes / 1024, 1).' KB';
    }

    public function leaseTypeLabel(): string
    {
        return self::LEASE_TYPES[$this->lease_type] ?? ucfirst(str_replace('_', ' ', $this->lease_type));
    }
}
