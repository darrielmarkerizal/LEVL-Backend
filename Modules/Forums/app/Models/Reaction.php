<?php

namespace Modules\Forums\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    use HasFactory;

     
    const TYPE_LIKE = 'like';

    const TYPE_HELPFUL = 'helpful';

    const TYPE_SOLVED = 'solved';

     
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reactable_type',
        'reactable_id',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

     
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

     
    public static function toggle(int $userId, string $reactableType, int $reactableId, string $type): bool
    {
        $reaction = static::where([
            'user_id' => $userId,
            'reactable_type' => $reactableType,
            'reactable_id' => $reactableId,
            'type' => $type,
        ])->first();

        if ($reaction) {
            $reaction->delete();

            return false;
        }

        static::create([
            'user_id' => $userId,
            'reactable_type' => $reactableType,
            'reactable_id' => $reactableId,
            'type' => $type,
        ]);

        return true;
    }

     
    public static function getTypes(): array
    {
        return [
            self::TYPE_LIKE,
            self::TYPE_HELPFUL,
            self::TYPE_SOLVED,
        ];
    }

     
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::getTypes());
    }
}
