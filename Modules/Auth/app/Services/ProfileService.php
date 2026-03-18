<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use App\Contracts\Services\ProfileServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\Services\AuthServiceInterface;
use Modules\Auth\Contracts\Services\EmailVerificationServiceInterface;
use Modules\Auth\Contracts\Services\ProfileStatisticsServiceInterface;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Events\AccountDeleted;
use Modules\Auth\Events\PasswordChanged;
use Modules\Auth\Events\ProfileUpdated;
use Modules\Auth\Models\User;

class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        private ProfilePrivacyService $privacyService,
        private UserActivityService $activityService,
        private EmailVerificationServiceInterface $emailVerification,
        private AuthServiceInterface $authService,
        private ProfileStatisticsServiceInterface $statisticsService
    ) {}

    public function updateProfile(User $user, array $data): User
    {
        if (array_key_exists('phone_number', $data) && ! array_key_exists('phone', $data)) {
            $data['phone'] = $data['phone_number'];
        }

        unset($data['phone_number']);

        $oldEmail = $user->email;

        $user->fill($data);
        $user->last_profile_update = now();
        $user->save();

        if (isset($data['email']) && $data['email'] !== $oldEmail) {
            $user->email_verified_at = null;
            $user->save();
        }

        \Illuminate\Support\Facades\Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);

        event(new ProfileUpdated($user, $oldEmail !== $user->email));

        return $user->fresh();
    }

    public function uploadAvatar(User $user, UploadedFile $file): string
    {
        $user->clearMediaCollection('avatar');

        $media = $user
            ->addMedia($file)
            ->toMediaCollection('avatar');

        $user->last_profile_update = now();
        $user->save();

        \Illuminate\Support\Facades\Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);

        return $media->getUrl();
    }

    public function deleteAvatar(User $user): void
    {
        $user->clearMediaCollection('avatar');
        $user->last_profile_update = now();
        $user->save();

        \Illuminate\Support\Facades\Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);
    }

    public function getProfileData(User $user, ?User $viewer = null): array
    {
        $viewer = $viewer ?? $user;

        $roleNames = $user->getRoleNames()->values();
        $primaryRole = Str::lower((string) ($roleNames->first() ?? 'student'));

        // Base data that's always visible
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'bio' => $user->bio,
            'location' => $user->location,
            'avatar_url' => $user->avatar_url,
            'status' => $user->status instanceof UserStatus ? $user->status->value : (string) $user->status,
            'role' => $primaryRole,
            'roles' => $roleNames->all(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        // Check if viewing own profile or if viewer is admin
        $isOwnProfile = $viewer->id === $user->id;
        $isAdmin = $viewer->hasRole('Admin') || $viewer->hasRole('Superadmin');

        if ($isOwnProfile || $isAdmin) {
            // Show all data for own profile or admin
            $data['email'] = $user->email;
            $data['phone'] = $user->phone;
            $data['email_verified_at'] = $user->email_verified_at;
            $data['last_profile_update'] = $user->last_profile_update;

            if ($primaryRole === 'student') {
                $data['statistics'] = $this->getStudentStatistics($user);
            }
        } else {
            // Filter data based on privacy settings for other viewers
            $privacySettings = $user->privacySettings ?? $this->privacyService->getPrivacySettings($user);

            // Add email if allowed
            if ($privacySettings->show_email) {
                $data['email'] = $user->email;
            }

            // Add phone if allowed
            if ($privacySettings->show_phone) {
                $data['phone'] = $user->phone;
            }

            // Add statistics if allowed and user is student
            if ($primaryRole === 'student' && $privacySettings->show_statistics) {
                $data['statistics'] = $this->getStudentStatistics($user);
            }

            // Note: activity_history and achievements will be handled by separate endpoints
            // that also check privacy settings
        }

        return $data;
    }

    /**
     * Get statistics for student profile
     */
    private function getStudentStatistics(User $user): array
    {
        // Get enrollment statistics
        $enrollmentStats = $this->statisticsService->getEnrollmentStats($user);

        // Get gamification statistics
        $gamificationStats = $user->gamificationStats;

        return [
            'total_courses' => $enrollmentStats['total_enrolled'] ?? 0,
            'completed_courses' => $enrollmentStats['total_completed'] ?? 0,
            'total_xp' => $gamificationStats->total_xp ?? 0,
            'current_level' => $gamificationStats->global_level ?? 1,
        ];
    }

    public function getPublicProfile(User $user, User $viewer): array
    {
        if (! $this->privacyService->canViewProfile($user, $viewer)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(__('messages.profile.no_permission'));
        }

        $profileData = $this->getProfileData($user, $viewer);

        $visibleFields = collect($user->getVisibleFieldsFor($viewer));

        return $profileData;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Current password validation is handled by ChangePasswordRequest
        // No need to check again here

        $user->password = Hash::make($newPassword);
        $user->save();

        \Illuminate\Support\Facades\Cache::tags(['user_profile'])->forget('user_profile:'.$user->id);

        event(new PasswordChanged($user));

        return true;
    }

    public function requestEmailChange(User $user, string $newEmail, ?string $ip, ?string $userAgent): ?string
    {
        $uuid = $this->emailVerification->sendChangeEmailLink($user, $newEmail);

        $this->authService->logEmailChangeRequest($user, $newEmail, $uuid, $ip, $userAgent);

        return $uuid;
    }

    public function verifyEmailChange(User $user, string $token, string $uuid): array
    {
        return $this->emailVerification->verifyChangeByToken($token, $uuid);
    }

    public function deleteAccount(User $user, string $password): bool
    {
        if (! Hash::check($password, $user->password)) {
            throw new \Exception(__('messages.auth.password_incorrect'));
        }

        $user->status = UserStatus::Inactive;
        $user->save();
        $user->delete();

        event(new AccountDeleted($user));

        return true;
    }

    public function restoreAccount(User $user): bool
    {
        if (! $user->trashed()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => [__('messages.account.restore_not_deleted')],
            ]);
        }

        $days = (int) ((\Modules\Common\Models\SystemSetting::get('auth_account_retention_days', 30)) ?? 30);

        if ($user->deleted_at->addDays($days)->isPast()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'account' => [__('messages.account.restore_expired', ['days' => $days])],
            ]);
        }

        $user->restore();
        $user->status = UserStatus::Active;
        $user->save();

        return true;
    }
}
