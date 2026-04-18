<?php

declare(strict_types=1);

namespace Modules\Auth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
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

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
