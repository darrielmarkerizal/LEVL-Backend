<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Collection;
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
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $now = now();

        $prefix = uniqid('b_', true);

        for ($i = 0; $i < 1000; $i++) {
            $users[] = [
                'name' => 'Benchmark User ' . $i,
                'username' => $prefix . '_' . $i,
                'email' => $prefix . '_' . $i . '@example.com',
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
        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $baseUsername = trim((string) ($payload['username'] ?? Str::slug($name, '_')));
        $suffix = Str::lower((string) Str::ulid());
        $now = now();

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
            'password' => $password,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function cleanupDatabase(): void
    {
        $this->repository->truncateUsers();
    }
}
