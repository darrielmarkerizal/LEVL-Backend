<?php

namespace Modules\Operations\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Report extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'type', 'generated_by', 'filters', 'notes', 'generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'generated_at' => 'datetime',
    ];

    protected $appends = ['file_url'];

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('report')
            ->singleFile()
            ->useDisk('do')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
            ]);
    }

    public function getFileUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('report');

        return $media?->getUrl();
    }

    public function generator()
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'generated_by');
    }
}
