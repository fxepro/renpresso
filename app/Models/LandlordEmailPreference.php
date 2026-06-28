<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordEmailPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'landlord_id', 'email_template_id',
        'is_enabled', 'subject_override', 'body_html_override',
    ];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /** Resolved subject: override → platform default. */
    public function resolvedSubject(): string
    {
        return $this->subject_override ?: $this->template->subject;
    }

    /** Resolved body: override → platform default. */
    public function resolvedBody(): string
    {
        return $this->body_html_override ?: $this->template->body_html;
    }
}
