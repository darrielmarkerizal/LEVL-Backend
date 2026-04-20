<?php

declare(strict_types=1);

namespace Modules\Auth\Observers;

use Modules\Auth\Models\ProfilePrivacySetting;
use Modules\Auth\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        
        ProfilePrivacySetting::create([
            'user_id' => $user->id,
            'profile_visibility' => ProfilePrivacySetting::VISIBILITY_PUBLIC,
            'show_email' => $this->pgsqlBool(false),
            'show_phone' => $this->pgsqlBool(false),
            'show_activity_history' => $this->pgsqlBool(true),
            'show_achievements' => $this->pgsqlBool(true),
            'show_statistics' => $this->pgsqlBool(true),
        ]);
    }

    private function pgsqlBool(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
    }
}
