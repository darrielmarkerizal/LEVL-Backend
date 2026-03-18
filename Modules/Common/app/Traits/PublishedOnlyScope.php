<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Database\Eloquent\Builder;

trait PublishedOnlyScope
{
    protected static function bootPublishedOnlyScope()
    {
        static::addGlobalScope('published_only', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return;
            }

            $user = auth('api')->user();

            if (! $user || $user->hasRole('Student')) {
                $builder->whereIn($builder->getModel()->getTable().'.status', ['published', 'active']);
            }
        });
    }
}
