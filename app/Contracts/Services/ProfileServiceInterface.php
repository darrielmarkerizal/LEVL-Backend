<?php

namespace App\Contracts\Services;

use Illuminate\Http\UploadedFile;
use Modules\Auth\Models\User;

interface ProfileServiceInterface
{
    
    public function updateProfile(User $user, array $data): User;

    
    public function uploadAvatar(User $user, UploadedFile $file): string;

    
    public function deleteAvatar(User $user): void;

    
    public function getProfileData(User $user, ?User $viewer = null): array;

    
    public function getPublicProfile(User $user, User $viewer): array;

    
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;

    
    public function deleteAccount(User $user, string $password): bool;

    
    public function restoreAccount(User $user): bool;

    
    public function requestEmailChange(User $user, string $newEmail, ?string $ip, ?string $userAgent): ?string;

    
    public function verifyEmailChange(User $user, string $token, string $uuid): array;
}
