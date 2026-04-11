<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RunBlackboxTests extends Command
{
    protected $signature = 'blackbox:run 
                            {file=blackbox_tests.json : Path to the generated JSON test file} 
                            {--url= : Base API URL (default: APP_URL context)}
                            {--module= : Filter by module name (e.g. Auth)}
                            {--role= : Test specific role scenarios only}
                            {--disable-throttle : Disable throttle middleware during this run}
                            {--with-throttle : Keep throttle middleware enabled during this run}
                            {--dry-run : Only show what would be run without actually sending requests}';

    protected $description = 'Run automated black box tests against the local API based on generated JSON';

    // Dummy credentials for auto-login
    private array $roleCredentials = [
        'Superadmin' => ['login' => 'superadmin@example.com', 'password' => 'supersecret'],
        'Admin'      => ['login' => 'admin@example.com', 'password' => 'password'],
        'Instructor' => ['login' => 'instructor@example.com', 'password' => 'password'],
        'Student'    => ['login' => 'student@example.com', 'password' => 'password'],
    ];

    // Dummy path parameter replacements
    private array $pathReplacements = [
        'course'      => 'kursus-test-levl',
        'slug'        => 'test-slug',
        'id'          => 1,
        'user'        => 1,
        'unit'        => 1,
        'lesson'      => 1,
        'assignment'  => 1,
        'quiz'        => 1,
        'submission'  => 1,
        'thread'      => 1,
        'reply'       => 1,
        'trashBinId'  => 1,
    ];

    private array $tokens = [];
    private array $resolvedPathParams = [];
    private string $baseUrl = '';
    private bool $throttleBypassed = false;
    private string $reportFilePath = '';
    private float $startedAt = 0.0;
    private array $reportMeta = [];

    private int $statsPass = 0;
    private int $statsFail = 0;
    private int $statsSkip = 0;
    private int $statsDryRun = 0;
    private array $failures = [];
    private array $scenarioResults = [];

    public function handle(): int
    {
        $file = $this->argument('file');
        $this->startedAt = microtime(true);
        $this->initializeReport($file);

        try {
            if (!file_exists($file)) {
                $this->error("❌ File JSON tidak ditemukan: {$file}");
                $this->reportMeta['status'] = 'failed';
                $this->recordReportError("File JSON tidak ditemukan: {$file}");

            $this->info("🚀 Memulai Blackbox Runner API: {$this->baseUrl}");

        $this->statsPass++;
        $this->line("   <info>✓ PASS</info> - {$scenario}");
                $this->disableThrottleMiddleware();
            } else {
                $this->line('ℹ️ Menjalankan blackbox dengan throttle aktif (--with-throttle).');
            }

            if (!$this->option('dry-run')) {
                $this->authenticateRoles();
            }

            $data = json_decode(file_get_contents($file), true);
            if (!$data) {
                $this->error("❌ Gagal parsing JSON file.");
                $this->reportMeta['status'] = 'failed';
                $this->recordReportError('Gagal parsing JSON file.');

                return self::FAILURE;
            }

            $moduleFilter = $this->option('module');
            if ($moduleFilter) {
                $data = array_filter($data, fn($ep) => strtolower($ep['module']) === strtolower($moduleFilter));
            }

            $data = array_values($data);

            $this->info(sprintf("Ditemukan %d endpoint untuk diuji.", count($data)));
            $this->newLine();

            foreach ($data as $endpoint) {
                $this->testEndpoint($endpoint);
            }

            $this->reportMeta['status'] = $this->statsFail === 0 ? 'passed' : 'failed';
            $this->printSummary();

            return $this->statsFail === 0 ? self::SUCCESS : self::FAILURE;
        } finally {
            $this->saveReport();
        }
    }

    private function disableThrottleMiddleware(): void
    {
        $router = app('router');

        $router->aliasMiddleware('throttle', \App\Http\Middleware\BypassThrottleForBlackbox::class);
        $this->throttleBypassed = true;

        $this->warn('⚠️ Middleware throttle dinonaktifkan untuk proses blackbox ini.');
    }

    /**
     * Authenticate dummy users for each role to obtain JWT tokens
     */
    private function authenticateRoles(): void
    {
        $this->info('🔑 Mendapatkan JWT Tokens untuk Role...');
        $bar = $this->output->createProgressBar(count($this->roleCredentials));

        foreach ($this->roleCredentials as $role => $creds) {
            try {
                $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
                $request = \Illuminate\Http\Request::create($this->baseUrl . '/api/v1/auth/login', 'POST', $creds);
                $request->headers->set('Accept', 'application/json');

                $response = $kernel->handle($request);

                if ($response->getStatusCode() === 200) {
                    $json = json_decode($response->getContent(), true);
                    $token = $json['data']['access_token'] ?? null;
                    if ($token) {
                        $this->tokens[$role] = $token;
                    } else {
                        $this->warn("\n⚠️ Token tidak ditemukan di payload untuk {$role}.");
                    }
                } else {
                    $this->warn("\n⚠️ Gagal login untuk role {$role} ({$creds['login']}). HTTP " . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                 $this->warn("\n⚠️ Gagal koneksi untuk role {$role}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (empty($this->tokens)) {
            $this->error('Gagal mendapatkan token apa pun. Testing untuk endpoint yang terautentikasi akan gagal.');
            $this->newLine();
        }
    }

    /**
     * Test a single endpoint (Positive + Negative Scenarios)
     */
    private function testEndpoint(array $endpoint): void
    {
        $method = $endpoint['method'];
        $originalUri = $endpoint['uri'];
        $module = $endpoint['module'];

        // Replace Path Parameters
        $uri = $this->normalizeStaticUriSegments($this->substitutePathParams($originalUri));
        $url = "{$this->baseUrl}/{$uri}";

        $this->info("⚙️  [{$module}] {$method} {$uri}");

        // Determine token to use
        $token = null;
        $roleUsed = 'None';
        if ($endpoint['requires_auth']) {
            $roles = $endpoint['required_roles'] ?? [];
            if (empty($roles)) {
                // If auth is required but no specific role, just use the first available token
                $token = reset($this->tokens);
                $roleUsed = array_key_first($this->tokens);
            } else {
                foreach ($roles as $role) {
                    if (isset($this->tokens[$role])) {
                        $token = $this->tokens[$role];
                        $roleUsed = $role;
                        break;
                    }
                }
            }
        }

        $positiveData = $this->preparePositiveData($endpoint['valid_body'] ?? [], $endpoint);

        $invalidCases = is_array($endpoint['invalid_cases'] ?? null) ? $endpoint['invalid_cases'] : [];
        $authCases = [];
        $otherCases = [];

        foreach ($invalidCases as $case) {
            $expectedStatus = $case['expected_status'] ?? null;
            if ($expectedStatus === 401 || $expectedStatus === 403) {
                $authCases[] = $case;
                continue;
            }

            $otherCases[] = $case;
        }

        foreach ($authCases as $case) {
            $expectedStatus = $case['expected_status'];
            $scenarioName   = $case['scenario'];
            $scenarioBody   = $case['body'] ?? [];

            if ($expectedStatus === 401 && !$this->expectsAuthMiddleware($endpoint)) {
                $this->markAsSkipped($scenarioName . ' (endpoint tidak mewajibkan auth)', $method . ' ' . $uri);
                continue;
            }

            if ($expectedStatus === 403 && !$this->expectsRoleOrPolicyMiddleware($endpoint)) {
                $this->markAsSkipped($scenarioName . ' (endpoint tidak mewajibkan role/policy)', $method . ' ' . $uri);
                continue;
            }
            
            // For negative tests related to roles (401/403), adjust the token logic
            $caseToken = $token;
            if ($expectedStatus === 401) {
                $caseToken = null; // Unauthenticated
                $scenarioBody = $positiveData;
            } elseif ($expectedStatus === 403) {
                // Try to find a token for a role that is NOT in required_roles
                $caseToken = $this->getForbiddenToken($endpoint['required_roles']);
                $scenarioBody = $positiveData;
            }

            $this->runScenario($scenarioName, $uri, $url, $method, $scenarioBody, $caseToken, [$expectedStatus]);
        }

        // Positive test is executed after auth tests so destructive endpoints do not break 401/403 assertions.
        $this->runScenario("Skenario Positif (Valid Body) - Role: {$roleUsed}", $uri, $url, $method, $positiveData, $token, [200, 201, 202, 204]);

        foreach ($otherCases as $case) {
            $expectedStatus = $case['expected_status'];
            $scenarioName   = $case['scenario'];
            $scenarioBody   = $case['body'] ?? [];

            $this->runScenario($scenarioName, $uri, $url, $method, $scenarioBody, $token, [$expectedStatus]);
        }
        
        $this->newLine();
    }

    private function preparePositiveData(array $body, array $endpoint): array
    {
        $data = $body;
        $rules = is_array($endpoint['validation_rules'] ?? null) ? $endpoint['validation_rules'] : [];

        $suffix = (string) now()->timestamp . random_int(100, 999);

        $stringUniqueKeys = ['code', 'username', 'email', 'slug', 'value'];
        foreach ($stringUniqueKeys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            if (!is_string($data[$key]) || $data[$key] === '') {
                continue;
            }

            if ($key === 'email') {
                $data[$key] = "blackbox.{$suffix}@levl.test";
                continue;
            }

            $data[$key] = strtolower(preg_replace('/[^a-z0-9\-_.]/i', '-', $data[$key])) . "-{$suffix}";
        }

        if (array_key_exists('level', $data) && is_numeric($data['level'])) {
            $data['level'] = (int) $data['level'] + random_int(200, 5000);
        }

        if (($endpoint['uri'] ?? '') === 'api/v1/tags' && !array_key_exists('names', $data)) {
            if (array_key_exists('name', $data) && is_string($data['name']) && $data['name'] !== '') {
                $data['names'] = [$data['name'] . "-{$suffix}"];
            }
        }

        foreach ($rules as $field => $ruleDef) {
            if (!is_string($ruleDef)) {
                continue;
            }

            if (!array_key_exists($field, $data)) {
                if (str_contains($ruleDef, 'required') && str_contains($ruleDef, 'array')) {
                    $data[$field] = ['blackbox-' . $suffix];
                }

                continue;
            }

            if (str_contains($ruleDef, 'array') && !is_array($data[$field])) {
                $data[$field] = [$data[$field]];
            }

            if (str_contains($ruleDef, 'in:') && !is_array($data[$field])) {
                preg_match('/in:([^|]+)/', $ruleDef, $matches);
                $allowed = isset($matches[1]) ? array_map('trim', explode(',', $matches[1])) : [];
                if (!empty($allowed) && !in_array((string) $data[$field], $allowed, true)) {
                    $data[$field] = $allowed[0];
                }
            }
        }

        return $data;
    }

    /**
     * Get a token for a role that is NOT in the allowed roles array
     */
    private function getForbiddenToken(array $allowedRoles): ?string
    {
        $allowedRolesLower = array_map('strtolower', $allowedRoles);
        foreach ($this->tokens as $role => $token) {
            if (!in_array(strtolower($role), $allowedRolesLower)) {
                return $token;
            }
        }
        // If we only have allowed tokens, return a dummy invalid token to trigger 403 or 401
        return 'invalid-token-for-forbidden';
    }

    /**
     * Run an individual HTTP Request scenario
     */
    private function runScenario(string $scenario, string $api, string $url, string $method, array $body, ?string $token, array $expectedStatuses): void
    {
        if ($this->option('dry-run')) {
            $this->statsDryRun++;
            $this->line("   - [DRY] {$scenario}");
            $this->scenarioResults[] = [
                'api' => $api,
                'scenario' => $scenario,
                'status' => 'dry_run',
                'expected' => implode(',', $expectedStatuses),
                'actual' => null,
                'response' => null,
            ];
            return;
        }

        if ($this->throttleBypassed) {
            $this->clearRateLimiterState();
        }

        // Prevent auth guard state from leaking across scenarios in the same process.
        app('auth')->forgetGuards();
        Auth::shouldUse('api');

        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        $upperMethod = strtoupper($method);

        // Prepare request payload
        $parameters = $upperMethod === 'GET' ? $body : [];
        $content = $upperMethod !== 'GET' ? json_encode($body) : null;
        $files = [];
        $temporaryFiles = [];

        if ($upperMethod !== 'GET') {
            [$parameters, $files, $temporaryFiles] = $this->prepareBodyAndFiles($body);
            if (!empty($files)) {
                $content = null;
            } else {
                $parameters = [];
            }
        }

        // Bypass ThrottleRequests limit by simulating a random IP address
        $server = [
            'REMOTE_ADDR' => '127.0.0.' . rand(1, 255),
        ];

        $request = \Illuminate\Http\Request::create($url, $upperMethod, $parameters, [], $files, $server, $content);
        $request->setUserResolver(static fn () => null);
        $request->headers->set('Accept', 'application/json');
        
        if ($content !== null) {
            $request->headers->set('Content-Type', 'application/json');
        }

        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        try {
            $response = $kernel->handle($request);
            $actualStatus = $response->getStatusCode();
            $responseBody = $response->getContent();
        } catch (\Exception $e) {
            $this->markAsFailed($scenario, $api, "Internal Exception: " . $e->getMessage(), implode(',', $expectedStatuses), 'Exception');
            $this->cleanupTemporaryFiles($temporaryFiles);
            return;
        }

        $this->cleanupTemporaryFiles($temporaryFiles);

        if (in_array($actualStatus, $expectedStatuses)) {
            $this->markAsPassed($scenario, $api, (string) $actualStatus, $responseBody, implode(',', $expectedStatuses));
        } else {
            $this->markAsFailed($scenario, $api, $responseBody, implode(',', $expectedStatuses), (string) $actualStatus);
        }
    }

    private function prepareBodyAndFiles(array $body): array
    {
        $parameters = [];
        $files = [];
        $temporaryFiles = [];

        foreach ($body as $key => $value) {
            if ($this->shouldAttachDummyFile($key, $value)) {
                $file = $this->createDummyImageFile((string) $key);
                $files[$key] = $file;
                $temporaryFiles[] = $file->getPathname();
                continue;
            }

            $parameters[$key] = $value;
        }

        return [$parameters, $files, $temporaryFiles];
    }

    private function shouldAttachDummyFile(string $field, mixed $value): bool
    {
        $uploadFields = ['thumbnail', 'image', 'file', 'avatar'];

        if (!in_array(strtolower($field), $uploadFields, true)) {
            return false;
        }

        return $value === null || $value === '' || $value === [];
    }

    private function createDummyImageFile(string $field): UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'bbx_');
        $png1x1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgQf6jV4AAAAASUVORK5CYII=');
        file_put_contents($tmpPath, $png1x1);

        return new UploadedFile(
            $tmpPath,
            $field . '.png',
            'image/png',
            null,
            true
        );
    }

    private function cleanupTemporaryFiles(array $temporaryFiles): void
    {
        foreach ($temporaryFiles as $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function expectsAuthMiddleware(array $endpoint): bool
    {
        $runtimeMiddlewares = $this->getRuntimeMiddlewares($endpoint);
        if (!empty($runtimeMiddlewares)) {
            foreach ($runtimeMiddlewares as $middleware) {
                if (!is_string($middleware)) {
                    continue;
                }

                $value = strtolower($middleware);

                if (str_contains($value, 'auth:api') || str_starts_with($value, 'auth')) {
                    return true;
                }

                if (str_starts_with($value, 'role:') || str_starts_with($value, 'can:')) {
                    return true;
                }
            }

            return false;
        }

        if (($endpoint['requires_auth'] ?? false) === true) {
            return true;
        }

        $middlewares = $endpoint['middleware'] ?? [];
        foreach ($middlewares as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            $value = strtolower($middleware);

            if (str_contains($value, 'auth:api') || str_starts_with($value, 'auth')) {
                return true;
            }

            if (str_starts_with($value, 'role:') || str_starts_with($value, 'can:')) {
                return true;
            }
        }

        return false;
    }

    private function expectsRoleOrPolicyMiddleware(array $endpoint): bool
    {
        $runtimeMiddlewares = $this->getRuntimeMiddlewares($endpoint);
        if (!empty($runtimeMiddlewares)) {
            foreach ($runtimeMiddlewares as $middleware) {
                if (!is_string($middleware)) {
                    continue;
                }

                $value = strtolower($middleware);

                if (str_starts_with($value, 'role:') || str_starts_with($value, 'can:')) {
                    return true;
                }
            }

            return false;
        }

        $middlewares = $endpoint['middleware'] ?? [];
        foreach ($middlewares as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            $value = strtolower($middleware);

            if (str_starts_with($value, 'role:') || str_starts_with($value, 'can:')) {
                return true;
            }
        }

        return false;
    }

    private function getRuntimeMiddlewares(array $endpoint): array
    {
        try {
            $uri = $this->substitutePathParams($endpoint['uri'] ?? '');
            $path = '/' . ltrim($uri, '/');
            $method = strtoupper((string) ($endpoint['method'] ?? 'GET'));

            $request = \Illuminate\Http\Request::create($path, $method);
            $route = app('router')->getRoutes()->match($request);
            $middlewares = $route->gatherMiddleware();

            return is_array($middlewares) ? $middlewares : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function clearRateLimiterState(): void
    {
        try {
            $limiterStore = config('cache.limiter');

            if (is_string($limiterStore) && $limiterStore !== '') {
                Cache::store($limiterStore)->flush();
                return;
            }

            Cache::flush();
        } catch (\Throwable) {
        }
    }

    private function substitutePathParams(string $uri): string
    {
        return preg_replace_callback('/\{(\w+?)(?::([\w]+))?\}/', function ($matches) use ($uri) {
            $param = $matches[1];
            $bindingField = $matches[2] ?? null;

            return (string) $this->resolvePathParamValue($param, $bindingField, $uri);
        }, $uri);
    }

    private function resolvePathParamValue(string $param, ?string $bindingField, string $uri): string|int
    {
        $cacheKey = strtolower($param . '|' . ($bindingField ?? '') . '|' . $uri);
        if (array_key_exists($cacheKey, $this->resolvedPathParams)) {
            return $this->resolvedPathParams[$cacheKey];
        }

        $value = $this->resolvePathParamFromDatabase($param, $bindingField, $uri);
        if ($value === null) {
            $value = $this->pathReplacements[$param] ?? '1';
        }

        $this->resolvedPathParams[$cacheKey] = $value;

        return $value;
    }

    private function resolvePathParamFromDatabase(string $param, ?string $bindingField, string $uri): string|int|null
    {
        $normalizedParam = strtolower($param);
        $field = strtolower((string) $bindingField);

        return match ($normalizedParam) {
            'id' => $this->resolveGenericIdByUri($uri),
            'course' => $this->resolveCourseParam($field),
            'unit' => $this->resolveFirstValue('units', 'id'),
            'lesson' => $this->resolveFirstValue('lessons', 'id'),
            'assignment' => $this->resolveFirstValue('assignments', 'id'),
            'quiz' => $this->resolveFirstValue('quizzes', 'id'),
            'submission' => $this->resolveFirstValue('submissions', 'id'),
            'enrollment' => $this->resolveFirstValue('enrollments', 'id'),
            'user' => $this->resolveFirstValue('users', 'id'),
            'thread' => $this->resolveFirstValue('threads', 'id')
                ?? $this->resolveFirstValue('forum_threads', 'id'),
            'reply' => $this->resolveFirstValue('replies', 'id')
                ?? $this->resolveFirstValue('forum_replies', 'id'),
            'trashbinid' => $this->resolveFirstValue('trash_bins', 'id'),
            'contentid' => $this->resolveFirstValue('contents', 'id'),
            'post_uuid' => $this->resolveFirstValue('posts', 'uuid')
                ?? $this->resolveFirstValue('posts', 'id'),
            'slug' => $this->resolveSlugByUri($uri),
            default => null,
        };
    }

    private function resolveCourseParam(string $bindingField): string|int|null
    {
        if ($bindingField === 'id') {
            return $this->resolveFirstValue('courses', 'id');
        }

        return $this->resolveFirstValue('courses', 'slug')
            ?? $this->resolveFirstValue('courses', 'id');
    }

    private function resolveSlugByUri(string $uri): string|int|null
    {
        if (str_contains($uri, '/user/levels/')) {
            return $this->resolveFirstValue('courses', 'slug');
        }

        if (str_contains($uri, 'courses')) {
            return $this->resolveFirstValue('courses', 'slug');
        }

        if (str_contains($uri, 'units')) {
            return $this->resolveFirstValue('units', 'slug')
                ?? $this->resolveFirstValue('units', 'id');
        }

        if (str_contains($uri, 'lessons')) {
            return $this->resolveFirstValue('lessons', 'slug')
                ?? $this->resolveFirstValue('lessons', 'id');
        }

        return null;
    }

    private function resolveGenericIdByUri(string $uri): string|int|null
    {
        $map = [
            'categories' => ['categories', 'id'],
            'level-configs' => ['level_configs', 'id'],
            'tags' => ['tags', 'id'],
            'notifications' => ['notifications', 'id'],
            'posts' => ['posts', 'id'],
            'operations' => ['operations', 'id'],
            'master-data' => ['master_data', 'id'],
            'audit-logs' => ['activity_log', 'id'],
            'activity-logs' => ['activity_log', 'id'],
        ];

        foreach ($map as $segment => [$table, $column]) {
            if (str_contains($uri, $segment)) {
                return $this->resolveFirstValue($table, $column);
            }
        }

        return null;
    }

    private function normalizeStaticUriSegments(string $uri): string
    {
        $segments = explode('/', trim($uri, '/'));
        $count = count($segments);

        for ($i = 0; $i < $count; $i++) {
            if (!ctype_digit($segments[$i])) {
                continue;
            }

            if ($segments[$i] !== '1') {
                continue;
            }

            $resource = $segments[$i - 1] ?? '';
            $resolved = $this->resolveStaticIdForResource($resource);
            if ($resolved !== null) {
                $segments[$i] = (string) $resolved;
            }
        }

        return implode('/', $segments);
    }

    private function resolveStaticIdForResource(string $resource): string|int|null
    {
        $resource = strtolower($resource);

        return match ($resource) {
            'categories' => $this->resolveFirstValue('categories', 'id'),
            'level-configs' => $this->resolveFirstValue('level_configs', 'id'),
            'tags' => $this->resolveFirstValue('tags', 'id'),
            'notifications' => $this->resolveFirstValue('notifications', 'id'),
            'operations' => $this->resolveFirstValue('operations', 'id'),
            'master-data' => $this->resolveFirstValue('master_data', 'id'),
            'badges' => $this->resolveFirstValue('badges', 'id'),
            'levels' => $this->resolveFirstValue('level_configs', 'id'),
            'posts' => $this->resolveFirstValue('posts', 'id')
                ?? $this->resolveFirstValue('posts', 'uuid'),
            default => null,
        };
    }

    private function resolveFirstValue(string $table, string $column): string|int|null
    {
        try {
            $query = DB::table($table);

            if ($this->tableHasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            if ($column === 'slug') {
                $query->whereNotNull($column)->where($column, '!=', '');
            }

            $value = $query->orderBy('id')->value($column);
            if ($value === null) {
                return null;
            }

            return is_numeric($value) ? (int) $value : (string) $value;
        } catch (\Throwable) {
            return null;
        }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        try {
            $result = DB::selectOne(
                'SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = ? LIMIT 1',
                [$table, $column]
            );

            return $result !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    private function markAsPassed(string $scenario, string $api, string $actual, string $responseBody, string $expected): void
    {
        $this->statsPass++;
        $this->line("   <info>✓ PASS</info> - {$scenario}");
        $this->scenarioResults[] = [
            'api' => $api,
            'scenario' => $scenario,
            'status' => 'pass',
            'expected' => $expected,
            'actual' => $actual,
            'response' => $responseBody,
        ];
    }

    private function markAsSkipped(string $scenario, string $api): void
    {
        $this->statsSkip++;
        $this->line("   <comment>↷ SKIP</comment> - {$scenario}");
        $this->scenarioResults[] = [
            'api' => $api,
            'scenario' => $scenario,
            'status' => 'skip',
            'expected' => null,
            'actual' => null,
            'response' => null,
        ];
    }

    private function markAsFailed(string $scenario, string $api, string $responseBody, string $expected, string $actual): void
    {
        $this->statsFail++;
        $this->line("   <error>✗ FAIL</error> - {$scenario}");
        $this->line("     Expected Status: {$expected} | Actual: {$actual}");

        $snippet = substr($responseBody, 0, 200);
        if (strlen($responseBody) > 200) {
            $snippet .= '...';
        }
        if (!empty($snippet)) {
            $this->line("     Response: {$snippet}");
        }

        $this->failures[] = [
            'api' => $api,
            'scenario' => $scenario,
            'expected' => $expected,
            'actual' => $actual,
            'response' => $responseBody,
        ];

        $this->scenarioResults[] = [
            'api' => $api,
            'scenario' => $scenario,
            'status' => 'fail',
            'expected' => $expected,
            'actual' => $actual,
            'response' => $responseBody,
        ];
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info("📊 --- HASIL PENGUJIAN BLACKBOX ---");

        if ($this->option('dry-run')) {
            $this->table(
                ['Metrik', 'Jumlah'],
                [
                    ['Total Skenario [DRY-RUN]', $this->statsDryRun],
                ]
            );
            $this->info("\nℹ️ Pengujian hanya disimulasikan (--dry-run). Hapus flag tersebut untuk rilis test nyata.");
        } else {
            $this->table(
                ['Metrik', 'Jumlah'],
                [
                    ['PASS ✅', $this->statsPass],
                    ['FAIL ❌', $this->statsFail],
                    ['SKIP ⏭️', $this->statsSkip],
                    ['Total Skenario', $this->statsPass + $this->statsFail + $this->statsSkip],
                ]
            );

            if ($this->statsFail > 0) {
                $this->error("\n⚠️ Ditemukan {$this->statsFail} pengujian yang gagal. Cek log di atas untuk detail.");
            } else {
                $this->info("\n🎉 Semua pengujian berhasil!");
            }
        }

        if ($this->reportFilePath !== '') {
            $this->line("📁 Laporan tersimpan: {$this->reportFilePath}");
        }
    }

    private function initializeReport(string $inputFile): void
    {
        $timestamp = now()->format('Ymd_His');
        $suffix = random_int(100, 999);
        $this->reportFilePath = storage_path('app/blackbox-results/blackbox-run-' . $timestamp . '-' . $suffix . '.json');
        $this->reportMeta = [
            'status' => 'running',
            'input_file' => $inputFile,
            'base_url' => null,
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'duration_seconds' => null,
            'options' => [
                'module' => $this->option('module'),
                'role' => $this->option('role'),
                'dry_run' => (bool) $this->option('dry-run'),
                'with_throttle' => (bool) $this->option('with-throttle'),
                'disable_throttle' => (bool) $this->option('disable-throttle'),
            ],
            'summary' => [],
            'errors' => [],
            'failures' => [],
        ];
    }

    private function recordReportError(string $message): void
    {
        $this->reportMeta['errors'][] = $message;
    }

    private function saveReport(): void
    {
        $this->reportMeta['finished_at'] = now()->toIso8601String();
        $this->reportMeta['duration_seconds'] = round(microtime(true) - $this->startedAt, 3);
        $this->reportMeta['summary'] = [
            'pass' => $this->statsPass,
            'fail' => $this->statsFail,
            'skip' => $this->statsSkip,
            'dry_run' => $this->statsDryRun,
            'total' => $this->statsPass + $this->statsFail + $this->statsSkip + $this->statsDryRun,
        ];
        $this->reportMeta['results'] = $this->scenarioResults;
        $this->reportMeta['failures'] = $this->failures;

        $directory = dirname($this->reportFilePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(
            $this->reportFilePath,
            json_encode($this->reportMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
