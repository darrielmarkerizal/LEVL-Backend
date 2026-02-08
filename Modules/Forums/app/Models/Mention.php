<?php

declare(strict_types=1);

namespace Modules\Forums\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mentionable_type',
        'mentionable_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }
}
