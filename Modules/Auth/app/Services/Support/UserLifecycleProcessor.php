<?php

declare(strict_types=1);

namespace Modules\Auth\Services\Support;

use App\Jobs\CreateAuditJob;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Events\UserStatusChanged;
use Modules\Auth\Models\User;
use Modules\Auth\Services\UserCacheService;
use Modules\Mail\Mail\Auth\UserCredentialsMail;

class UserLifecycleProcessor
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
        private readonly UserCacheService $cacheService,
        private readonly UserFinder $finder
    ) {}

    public function updateUserStatus(User $authUser, int $userId, string $status): User
    {
        $user = $this->finder->showUser($authUser, $userId);

        if ($status === UserStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => [__('messages.auth.status_cannot_be_pending')],
            ]);
        }

        if ($user->status === UserStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => [__('messages.auth.status_cannot_be_changed_from_pending')],
            ]);
        }

        return DB::transaction(function () use ($authUser, $user, $status) {
            $oldStatus = $user->status;
            $newStatus = UserStatus::from($status);

            $user->status = $newStatus;

            
            unset($user['learning_statistics']);
            unset($user['last_login_at']);
            unset($user['rank']);
            unset($user['total_xp']);

            $user->save();

            
            event(new UserStatusChanged($user, $oldStatus, $newStatus, $authUser));

            $this->cacheService->invalidateUser($user->id);
            $this->cacheService->invalidateAllUsers();

            return $user->fresh();
        });
    }

    public function updateUser(User $authUser, int $userId, array $data): User
    {
        $user = $this->finder->showUser($authUser, $userId);

        return DB::transaction(function () use ($authUser, $user, $data) {
            $updated = false;

            
            if (! empty($data['username'] ?? null)) {
                $user->username = $data['username'];
                $updated = true;
            }

            
            if (! empty($data['status'] ?? null)) {
                if ($data['status'] === UserStatus::Pending->value) {
                    throw ValidationException::withMessages([
                        'status' => [__('messages.auth.status_cannot_be_pending')],
                    ]);
                }

                if ($user->status === UserStatus::Pending) {
                    throw ValidationException::withMessages([
                        'status' => [__('messages.auth.status_cannot_be_changed_from_pending')],
                    ]);
                }

                $oldStatus = $user->status;
                $newStatus = UserStatus::from($data['status']);

                $user->status = $newStatus;
                $updated = true;

                
                event(new UserStatusChanged($user, $oldStatus, $newStatus, $authUser));
            }

            
            if (! empty($data['role'] ?? null)) {
                $targetRole = $data['role'];

                if ($authUser->hasRole('Admin') && ! $authUser->hasRole('Superadmin')) {
                    
                    if (! in_array($targetRole, ['Student', 'Instructor', 'Admin'], true)) {
                        throw new AuthorizationException(__('messages.forbidden'));
                    }
                } elseif ($authUser->hasRole('Superadmin')) {
                    if (! in_array($targetRole, ['Student', 'Instructor', 'Admin', 'Superadmin'], true)) {
                        throw new AuthorizationException(__('messages.forbidden'));
                    }
                } else {
                    throw new AuthorizationException(__('messages.unauthorized'));
                }

                if (! $user->hasRole($targetRole)) {
                    $user->syncRoles([$targetRole]);
                    $updated = true;
                }
            }

            
            if (! empty($data['password'] ?? null)) {
                if (! $authUser->can('resetPassword', $user)) {
                    throw new AuthorizationException(__('messages.forbidden'));
                }

                $user->password = Hash::make($data['password']);
                $user->is_password_set = true;
                $updated = true;
            }

            
            if (array_key_exists('specialization_id', $data)) {
                $user->specialization_id = $data['specialization_id'];
                $updated = true;
            }

            if ($updated) {
                
                unset($user['learning_statistics']);
                unset($user['last_login_at']);
                unset($user['rank']);
                unset($user['total_xp']);

                $user->save();
                $this->cacheService->invalidateUser($user->id);
                $this->cacheService->invalidateAllUsers();
            }

            return $user->fresh()->load('specialization:id,name,value');
        });
    }

    public function resetPassword(User $authUser, int $userId, string $newPassword): User
    {
        $user = $this->finder->showUser($authUser, $userId);

        
        if (! auth()->user()->can('resetPassword', $user)) {
            throw new AuthorizationException(__('messages.forbidden'));
        }

        return DB::transaction(function () use ($user, $newPassword) {
            $user->password = Hash::make($newPassword);
            $user->is_password_set = true;

            // Hapus virtual attributes yang di-set oleh hydrateInstructorDetail/hydrateStudentDetail
            // agar tidak ikut di-persist ke DB
            unset($user['learning_statistics']);
            unset($user['last_login_at']);
            unset($user['rank']);
            unset($user['total_xp']);

            $user->save();

            
            $this->revokeAllUserTokens($user);

            $this->cacheService->invalidateUser($user->id);

            return $user->fresh();
        });
    }

    
    private function revokeAllUserTokens(User $user): void
    {
        try {
            
            DB::table('refresh_tokens')
                ->where('user_id', $user->id)
                ->delete();

        } catch (\Exception $e) {
            
            \Illuminate\Support\Facades\Log::warning('Failed to revoke tokens for user '.$user->id.': '.$e->getMessage());
        }
    }

    public function deleteUser(User $authUser, int $userId): void
    {
        $user = $this->finder->showUser($authUser, $userId);

        if ($user->id === $authUser->id) {
            throw ValidationException::withMessages([
                'account' => [__('messages.auth.cannot_delete_self')],
            ]);
        }

        if (! $authUser->hasRole('Superadmin')) {
            if ($user->hasRole('Superadmin')) {
                throw new AuthorizationException(__('messages.forbidden'));
            }
        }

        $this->cacheService->invalidateUser($user->id);
        $user->delete();
        $this->cacheService->invalidateAllUsers();
    }

    public function createUser(User $authUser, array $validated): User
    {
        $role = $validated['role'];

        if ($authUser->hasRole('Admin') && ! $authUser->hasRole('Superadmin')) {
            if (! in_array($role, ['Student', 'Admin', 'Instructor'])) {
                throw new AuthorizationException(__('messages.forbidden'));
            }
        } elseif ($authUser->hasRole('Superadmin')) {
            if (! in_array($role, ['Student', 'Superadmin', 'Admin', 'Instructor'])) {
                throw new AuthorizationException(__('messages.forbidden'));
            }
        } else {
            throw new AuthorizationException(__('messages.unauthorized'));
        }

        $passwordPlain = !empty($validated['password']) ? $validated['password'] : Str::random(12);
        $isPasswordSet = !empty($validated['password']);

        if (empty($validated['username'])) {
            $validated['username'] = $this->generateUniqueUsername($validated['name'], $validated['email']);
        }

        unset($validated['role'], $validated['password']);
        $validated['password'] = Hash::make($passwordPlain);

        $user = $this->authRepository->createUser($validated + ['is_password_set' => $isPasswordSet]);
        $user->assignRole($role);

        $this->sendCredentialsEmail($user, $passwordPlain);

        $this->cacheService->invalidateAllUsers();

        return $user->fresh()->load('specialization:id,name,value');
    }

    public function updateProfile(User $user, array $validated, ?string $ip, ?string $userAgent): User
    {
        return DB::transaction(function () use ($user, $validated, $ip, $userAgent) {
            $changes = [];
            foreach (['name', 'username'] as $field) {
                if (isset($validated[$field]) && $user->{$field} !== $validated[$field]) {
                    $changes[$field] = [$user->{$field}, $validated[$field]];
                    $user->{$field} = $validated[$field];
                }
            }

            $user->save();

            if (! empty($changes)) {
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

            return $user->fresh();
        });
    }

    protected function sendCredentialsEmail(User $user, string $passwordPlain): void
    {
        $loginUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/').'/login';

        Mail::to($user->email)
            ->queue((new UserCredentialsMail($user, $passwordPlain, $loginUrl))->onQueue('emails-critical'));
    }

    protected function generateUniqueUsername(string $name, string $email): string
    {
        $baseUsername = $this->sanitizeUsername($name);

        if (empty($baseUsername)) {
            $baseUsername = explode('@', $email)[0];
            $baseUsername = $this->sanitizeUsername($baseUsername);
        }

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }

    protected function sanitizeUsername(string $input): string
    {
        $username = strtolower($input);
        $username = preg_replace('/[^a-z0-9_\.\-]/', '', $username);
        $username = preg_replace('/[_\.\-]+/', '_', $username);
        $username = trim($username, '_.-');

        return substr($username, 0, 50);
    }
}
