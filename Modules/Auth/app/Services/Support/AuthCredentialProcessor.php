<?php

declare(strict_types=1);

namespace Modules\Auth\Services\Support;

use App\Jobs\CreateAuditJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class AuthCredentialProcessor
{
    public function setUsername(User $user, string $username): array
    {
        $user->update(['username' => $username]);
        $user->refresh();

        Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);

        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames()->values();
        $userArray['avatar_url'] = $user->avatar_url;

        return ['user' => $userArray];
    }

    public function setPassword(User $user, string $password): array
    {
        $user->update([
            'password' => Hash::make($password),
            'is_password_set' => true,
        ]);
        $user->refresh();

        Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);

        $userArray = $user->toArray();
        $userArray['roles'] = $user->getRoleNames()->values();
        $userArray['avatar_url'] = $user->avatar_url;

        return ['user' => $userArray];
    }

    public function logProfileUpdate(
        User $user,
        array $changes,
        ?string $ip,
        ?string $userAgent,
    ): void {
        dispatch(new CreateAuditJob([
            'action' => 'profile_update',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'actor_type' => User::class,
            'actor_id' => $user->id,
            'user_id' => $user->id,
            'context' => [
                'changes' => $changes,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ],
        ]));
    }

    public function logEmailChangeRequest(
        User $user,
        string $newEmail,
        string $uuid,
        ?string $ip,
        ?string $userAgent,
    ): void {
        dispatch(new CreateAuditJob([
            'action' => 'email_change_request',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'actor_type' => User::class,
            'actor_id' => $user->id,
            'user_id' => $user->id,
            'context' => [
                'new_email' => $newEmail,
                'uuid' => $uuid,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ],
        ]));
    }
}
