<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType  = 'string';
    public    $incrementing = false;

    protected $fillable = ['property_id', 'lease_id', 'sender_id', 'body', 'read_at', 'emailed_at'];

    protected function casts(): array
    {
        return [
            'read_at'    => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function lease()    { return $this->belongsTo(Lease::class); }
    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }

    public function isPropertyBroadcast(): bool
    {
        return $this->property_id !== null && $this->lease_id === null;
    }

    /** Messages visible in a tenant/landlord lease thread (lease posts + property broadcasts). */
    public function scopeForLeaseThread($query, Lease $lease)
    {
        return $query->where(function ($q) use ($lease) {
            $q->where('lease_id', $lease->id)
                ->orWhere(function ($q2) use ($lease) {
                    $q2->where('property_id', $lease->property_id)
                        ->whereNull('lease_id');
                });
        });
    }
}
