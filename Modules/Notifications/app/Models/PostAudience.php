<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Notifications\Enums\PostAudienceRole;

class PostAudience extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'role',
    ];

    protected $casts = [
        'role' => PostAudienceRole::class,
    ];

    public $timestamps = false;

    protected $dates = [
        'created_at',
    ];

    // Relationships

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
