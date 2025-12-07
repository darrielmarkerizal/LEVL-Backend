<?php

namespace Modules\Operations\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certificate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id', 'course_id', 'certificate_number',
        'issued_at', 'expired_at', 'status',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected $appends = ['file_url'];

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificate')
            ->singleFile()
            ->useDisk('do')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    public function getFileUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('certificate');

        return $media?->getUrl();
    }

    public function user()
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Schemes\Models\Course::class);
    }
}
