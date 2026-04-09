<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Auth\Repositories\BenchmarkRepository;

class BenchmarkService
{
    public function __construct(
        private readonly BenchmarkRepository $repository
    ) {}

    public function getBenchmarkUsers(): Collection
    {
        return $this->repository->get1000Users();
    }

    public function createBenchmarkUsers(): bool
    {
        $users = [];
        $password = Hash::make('password');
        $now = now();

        for ($i = 0; $i < 1000; $i++) {
            $users[] = [
                'name' => 'Benchmark User '.Str::random(10),
                'username' => 'bench_'.Str::random(10).'_'.$i,
                'email' => 'bench_'.Str::random(10).'_'.$i.'@example.com',
                'password' => $password,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $this->repository->insert1000Users($users);
    }

    public function createBenchmarkUser(array $payload): User
    {
        $name = trim((string) ($payload['name'] ?? 'Benchmark User'));
        $baseEmail = trim((string) ($payload['email'] ?? 'bench@example.com'));
        $password = (string) ($payload['password'] ?? 'password123');
        $baseUsername = trim((string) ($payload['username'] ?? Str::slug($name, '_')));
        $suffix = Str::lower((string) Str::ulid());

        if ($name === '') {
            $name = 'Benchmark User';
        }

        if (! str_contains($baseEmail, '@')) {
            $baseEmail = 'bench@example.com';
        }

        [$localPart, $domainPart] = explode('@', $baseEmail, 2);
        $localPart = trim($localPart) !== '' ? trim($localPart) : 'bench';
        $domainPart = trim($domainPart) !== '' ? trim($domainPart) : 'example.com';

        $maxLocalPartLength = max(1, 191 - mb_strlen($domainPart) - mb_strlen($suffix) - 2);
        $localPart = Str::limit($localPart, $maxLocalPartLength, '');
        $email = $localPart.'+'.$suffix.'@'.$domainPart;

        if ($baseUsername === '') {
            $baseUsername = 'bench_user';
        }

        $username = Str::limit($baseUsername.'_'.$suffix, 50, '');

        return $this->repository->createUser([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
    }

    public function cleanupDatabase(): void
    {
        $this->repository->truncateUsers();
    }
}
