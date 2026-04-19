<?php

declare(strict_types=1);

namespace Modules\Auth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    
    protected $listen = [
        \Modules\Auth\Events\UserStatusChanged::class => [
            \Modules\Auth\Listeners\LogUserStatusChange::class,
            \Modules\Auth\Listeners\NotifyUserStatusChange::class,
        ],
        \Modules\Auth\Events\UserRegistered::class => [
            \Modules\Auth\Listeners\NotifyUserOnRegistered::class,
        ],
        \Modules\Auth\Events\UserLoggedIn::class => [
            \Modules\Auth\Listeners\NotifyUserOnLoggedIn::class,
        ],
        \Modules\Auth\Events\ProfileUpdated::class => [
            \Modules\Auth\Listeners\NotifyUserOnProfileUpdated::class,
        ],
        \Modules\Auth\Events\PasswordChanged::class => [
            \Modules\Auth\Listeners\NotifyUserOnPasswordChanged::class,
        ],
        \Modules\Auth\Events\AccountDeleted::class => [
            \Modules\Auth\Listeners\NotifyAdminsOnAccountDeleted::class,
        ],
    ];

    
    protected static $shouldDiscoverEvents = false;

    
    protected function configureEmailVerification(): void {}
}
