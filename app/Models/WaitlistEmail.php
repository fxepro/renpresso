<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WaitlistEmail extends Model
{
    use HasUuids;

    protected $keyType  = 'string';
    public    $incrementing = false;
    protected $table = 'waitlist_emails';

    protected $fillable = [
        'email', 'first_name', 'last_name', 'home_country',
        'property_countries', 'portfolio_size', 'pain_point', 'ref',
        'converted_user_id',
    ];

    public static function saveProfile(array $data): self
    {
        $email = $data['email'];
        $ref   = static::where('email', $email)->value('ref')
            ?? ('RMX-' . strtoupper(Str::random(6)));

        return static::updateOrCreate(
            ['email' => $email],
            [
                'first_name'         => $data['first_name'] ?? null,
                'last_name'          => $data['last_name'] ?? null,
                'home_country'       => $data['home_country'] ?? null,
                'portfolio_size'     => $data['portfolio_size'] ?? null,
                'property_countries' => $data['property_countries'] ?? null,
                'pain_point'         => $data['pain_point'] ?? null,
                'ref'                => $ref,
            ]
        );
    }

    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }
}
