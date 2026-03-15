<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'channel',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = [
        'created_at',
        'sent_at',
    ];

    // Relationships

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
