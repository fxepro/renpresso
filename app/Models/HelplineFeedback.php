<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelplineFeedback extends Model
{
    use HasUuids;

    protected $table = 'helpline_feedback';

    protected $fillable = [
        'user_id',
        'user_role',
        'working_well',
        'not_working',
        'additional',
        'rating',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
