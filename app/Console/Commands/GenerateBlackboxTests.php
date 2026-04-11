<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Enum as EnumRule;
use ReflectionMethod;

class GenerateBlackboxTests extends Command
{
    protected $signature   = 'blackbox:generate {--output=blackbox_tests.json} {--module= : Filter by module name (e.g., Auth, Schemes)}';
    protected $description = 'Auto-generate black box test data from all Levl-BE API routes and validation rules';

    // ──────────────────────────────────────────────
    // Valid dummy values — Levl LMS context
    // ──────────────────────────────────────────────
    private array $validValues = [
        // ─── Auth / Identity ──────────────────────
        'login'                => 'admin@levl.test',
        'email'                => 'testuser@levl.test',
        'password'             => 'Password123!',
        'password_confirmation'=> 'Password123!',
        'current_password'     => 'OldPassword123!',
        'new_password'         => 'NewPassword123!',
        'username'             => 'testuser_levl',
        'name'                 => 'Test User Levl',
        'full_name'            => 'Test Full Name',
        'role'                 => 'Student',
        'refresh_token'        => 'valid-refresh-token-string',

        // ─── Course / Scheme ──────────────────────
        'title'                => 'Kursus Test Levl',
        'short_desc'           => 'Deskripsi singkat kursus untuk pengujian blackbox.',
        'description'          => 'Deskripsi lengkap untuk pengujian yang cukup panjang sebagai validasi.',
        'code'                 => 'TEST-LEVL-001',
        'slug'                 => 'kursus-test-levl',
        'level_tag'            => 'dasar',
        'type'                 => 'okupasi',
        'enrollment_type'      => 'auto_accept',
        'enrollment_key'       => 'secret-key-123',
        'category_id'          => 1,
        'status'               => 'draft',
        'order'                => 1,
        'duration_minutes'     => 60,
        'markdown_content'     => '# Test Content\n\nIni konten markdown test.',
        'prereq'               => null,

        // ─── Unit / Lesson ────────────────────────
        'unit_id'              => 1,
        'lesson_id'            => 1,
        'course_id'            => 1,

        // ─── Learning / Assignment / Quiz ─────────
        'assignment_id'        => 1,
        'quiz_id'              => 1,
        'question_id'          => 1,
        'submission_id'        => 1,
        'content'              => 'Jawaban submission test dari pengujian blackbox.',
        'score'                => 85,
        'feedback'             => 'Jawaban cukup baik.',
        'maximum_score'        => 100,
        'question_text'        => 'Apa itu Laravel?',
        'question_type'        => 'multiple_choice',
        'question_weight'      => 1,
        'answer_key'           => 'A',
        'answer_options'       => '[{"label":"A","text":"Framework PHP"},{"label":"B","text":"Database"}]',

        // ─── Grading ──────────────────────────────
        'grades'               => [['question_id' => 1, 'score' => 85, 'feedback' => 'Bagus']],

        // ─── Forum ────────────────────────────────
        'thread_id'            => 1,
        'reply_id'             => 1,

        // ─── Gamification ─────────────────────────
        'badge_id'             => 1,
        'points'               => 100,
        'xp'                   => 500,
        'level'                => 1,

        // ─── Enrollment ───────────────────────────
        'enrollment_id'        => 1,
        'user_id'              => 1,
        'user_ids'             => [1],
        'enrollment_ids'       => [1],

        // ─── Content / Announcement / News ────────
        'announcement_id'      => 1,
        'publish_at'           => '2026-04-15 10:00:00',
        'scheduled_at'         => '2026-04-15 10:00:00',
        'target_audience'      => 'all',
        'visibility'           => 'public',
        'is_pinned'            => false,
        'body'                 => 'Konten pengumuman test untuk pengujian.',

        // ─── Notifications ────────────────────────
        'notification_id'      => 1,
        'read_at'              => '2026-04-11T10:00:00Z',

        // ─── Common / Tags / Categories ───────────
        'tag'                  => 'test-tag',
        'tags'                 => ['tag-1', 'tag-2'],
        'instructor_id'        => 1,
        'instructor_ids'       => [1],
        'outcomes'             => ['Memahami dasar Laravel'],
        'specialization_id'    => 1,

        // ─── Trash ────────────────────────────────
        'trash_bin_id'         => 1,
        'ids'                  => [1, 2],
        'source_type'          => 'course',

        // ─── Media / File ─────────────────────────
        'image'                => null,
        'photo'                => null,
        'file'                 => null,
        'avatar'               => null,
        'thumbnail'            => null,
        'banner'               => null,
        'attachments'          => null,

        // ─── Search / Pagination ──────────────────
        'q'                    => 'Laravel',
        'search'               => 'test',
        'page'                 => 1,
        'per_page'             => 10,
        'sort'                 => '-created_at',
        'limit'                => 10,
        'filter'               => [],

        // ─── Generic ──────────────────────────────
        'id'                   => 1,
        'uuid'                 => '550e8400-e29b-41d4-a716-446655440000',
        'token'                => 'abcdef1234567890abcdef1234567890ab',
        'url'                  => 'https://levl.test',
        'message'              => 'Test message',
        'note'                 => 'Test note',
        'notes'                => 'Test notes',
        'reason'               => 'Test reason',
        'date'                 => '2026-04-11',
        'start_date'           => '2026-04-11',
        'end_date'             => '2026-04-18',
        'rating'               => 5,
        'amount'               => 100,
        'quantity'             => 1,
    ];

    // ──────────────────────────────────────────────
    // Routes to always skip (internal/dev/benchmark)
    // ──────────────────────────────────────────────
    private array $skipPatterns = [
        '_ignition',
        'sanctum',
        'benchmark',
        'dev/tokens',
        'telescope',
        'horizon',
    ];

    // ──────────────────────────────────────────────
    // Entry point
    // ──────────────────────────────────────────────
    public function handle(): int
    {
        $this->info('🔍 Scanning semua routes Levl-BE...');

        $moduleFilter = $this->option('module');
        $routes       = $this->collectRoutes($moduleFilter);
        $outputFile   = $this->option('output');

        $totalEndpoints   = count($routes);
        $withValidation   = count(array_filter($routes, fn ($r) => !empty($r['validation_rules'])));
        $requiresAuth     = count(array_filter($routes, fn ($r) => $r['requires_auth']));
        $totalInvalid     = array_sum(array_map(fn ($r) => count($r['invalid_cases']), $routes));

        file_put_contents(
            $outputFile,
            json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->newLine();
        $this->info("✅ Berhasil di-generate!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Endpoint', $totalEndpoints],
                ['Dengan Validasi', $withValidation],
                ['Butuh Autentikasi', $requiresAuth],
                ['Total Test Case Negatif', $totalInvalid],
                ['Total Test Case (Positif + Negatif)', $totalEndpoints + $totalInvalid],
            ]
        );
        $this->info("📄 Disimpan ke: {$outputFile}");

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────
    // Collect all API routes
    // ──────────────────────────────────────────────
    private function collectRoutes(?string $moduleFilter): array
    {
        $result = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (!str_starts_with($uri, 'api/')) {
                continue;
            }

            foreach ($this->skipPatterns as $pattern) {
                if (str_contains($uri, $pattern)) {
                    continue 2;
                }
            }

            if ($moduleFilter) {
                $action = $route->getAction();
                $controller = $action['controller'] ?? '';
                if (!str_contains($controller, "Modules\\{$moduleFilter}\\")) {
                    continue;
                }
            }

            $methods = array_filter($route->methods(), fn ($m) => $m !== 'HEAD');

            foreach ($methods as $method) {
                $action         = $route->getAction();
                $controllerInfo = $this->resolveController($action);
                $middlewares    = $route->middleware();
                $requiresAuth   = $this->requiresAuth($middlewares);
                $requiredRoles  = $this->extractRoles($middlewares);
                $validationRules = $this->extractValidationRules($action, $method);
                $module          = $this->resolveModule($controllerInfo['controller']);

                $entry = [
                    'uri'              => $uri,
                    'method'           => strtoupper($method),
                    'name'             => $route->getName() ?? '',
                    'module'           => $module,
                    'controller'       => $controllerInfo['controller'],
                    'action'           => $controllerInfo['action'],
                    'middleware'       => $middlewares,
                    'requires_auth'    => $requiresAuth,
                    'required_roles'   => $requiredRoles,
                    'path_params'      => $this->extractPathParams($uri),
                    'validation_rules' => $validationRules,
                    'valid_body'       => $this->buildValidBody($validationRules),
                    'invalid_cases'    => $this->buildInvalidCases($validationRules),
                ];

                if ($requiresAuth) {
                    $entry['invalid_cases'][] = [
                        'scenario'        => 'Akses tanpa token autentikasi (JWT)',
                        'body'            => $this->buildValidBody($validationRules),
                        'expected_status' => 401,
                        'expected_error'  => 'Unauthenticated',
                    ];
                }

                if (!empty($requiredRoles)) {
                    $entry['invalid_cases'][] = [
                        'scenario'        => 'Akses dengan role yang tidak diizinkan (expected: ' . implode(', ', $requiredRoles) . ')',
                        'body'            => $this->buildValidBody($validationRules),
                        'expected_status' => 403,
                        'expected_error'  => 'Forbidden',
                    ];
                }

                $result[] = $entry;
            }
        }

        return $result;
    }

    // ──────────────────────────────────────────────
    // Resolve controller & method name
    // ──────────────────────────────────────────────
    private function resolveController(array $action): array
    {
        if (isset($action['controller'])) {
            $parts = explode('@', $action['controller']);
            return [
                'controller' => $parts[0] ?? '',
                'action'     => $parts[1] ?? '',
            ];
        }
        return ['controller' => 'Closure', 'action' => ''];
    }

    // ──────────────────────────────────────────────
    // Resolve module name from controller FQCN
    // ──────────────────────────────────────────────
    private function resolveModule(string $controller): string
    {
        if (preg_match('/Modules\\\\(\w+)\\\\/', $controller, $m)) {
            return $m[1];
        }
        if (str_starts_with($controller, 'App\\')) {
            return 'App';
        }
        return 'Unknown';
    }

    // ──────────────────────────────────────────────
    // Extract validation rules
    // ──────────────────────────────────────────────
    private function extractValidationRules(array $action, string $method): array
    {
        if (!isset($action['controller'])) {
            return [];
        }

        $parts      = explode('@', $action['controller']);
        $className  = $parts[0] ?? null;
        $methodName = $parts[1] ?? null;

        if (!$className || !$methodName || !class_exists($className)) {
            return [];
        }

        // For GET endpoints, only check inline validate() calls (e.g., Search)
        if (in_array(strtoupper($method), ['GET', 'DELETE', 'HEAD', 'OPTIONS'])) {
            if (strtoupper($method) === 'GET') {
                return $this->rulesFromValidateCall($className, $methodName);
            }
            return [];
        }

        // 1️⃣ Try Form Request first (type-hinted parameter)
        $rules = $this->rulesFromFormRequest($className, $methodName);
        if (!empty($rules)) {
            return $rules;
        }

        // 2️⃣ Fallback: parse validate() call inside controller method
        return $this->rulesFromValidateCall($className, $methodName);
    }

    private function rulesFromFormRequest(string $className, string $methodName): array
    {
        try {
            $ref    = new ReflectionMethod($className, $methodName);
            $params = $ref->getParameters();

            foreach ($params as $param) {
                $type = $param->getType();
                if (!$type || $type->isBuiltin()) {
                    continue;
                }

                $typeName = $type->getName();
                if (!class_exists($typeName)) {
                    continue;
                }

                $parentClasses = class_parents($typeName);
                $isFormRequest = in_array('Illuminate\Foundation\Http\FormRequest', $parentClasses ?? []);

                if (!$isFormRequest) {
                    continue;
                }

                // Try to instantiate and call rules()
                try {
                    $instance = new $typeName();
                    if (method_exists($instance, 'rules')) {
                        $raw = @$instance->rules();
                        if (is_array($raw) && !empty($raw)) {
                            return $this->normalizeRules($raw);
                        }
                    }
                } catch (\Throwable) {
                    // rules() might call $this->route(), $this->input() etc.
                }

                // Fallback: parse from source
                $rules = $this->rulesFromFormRequestSource($typeName);
                if (!empty($rules)) {
                    return $rules;
                }
            }
        } catch (\Throwable) {
        }

        return [];
    }

    /**
     * Parse rules from FormRequest source code when instantiation fails.
     * Handles trait method delegation like $this->rulesLogin()
     */
    private function rulesFromFormRequestSource(string $formRequestClass): array
    {
        try {
            $ref  = new \ReflectionClass($formRequestClass);
            $file = $ref->getFileName();
            if (!$file) return [];

            $source = file_get_contents($file);

            // Find rules() method body
            if (!preg_match('/public\s+function\s+rules\s*\(\s*\)\s*:\s*array\s*\{(.*?)\n\s*\}/s', $source, $methodMatch)) {
                return [];
            }

            $rulesBody = $methodMatch[1];

            // Case 1: return $this->rulesXxx(...) — delegate to trait
            if (preg_match('/return\s+\$this->(rules\w+)\s*\(/', $rulesBody, $traitMatch)) {
                $traitMethod = $traitMatch[1];

                foreach ($ref->getTraitNames() as $traitName) {
                    $traitRef = new \ReflectionClass($traitName);
                    if ($traitRef->hasMethod($traitMethod)) {
                        $traitFile = $traitRef->getFileName();
                        if (!$traitFile) continue;

                        $traitSource = file_get_contents($traitFile);
                        return $this->parseRulesFromMethodSource($traitSource, $traitMethod);
                    }
                }
            }

            // Case 2: Direct return [...] array
            return $this->parseRulesArrayFromSource($rulesBody);

        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Extract rules from a specific method in source code.
     */
    private function parseRulesFromMethodSource(string $source, string $methodName): array
    {
        // Find the method body — match until closing brace at indent level 4
        $pattern = '/(?:protected|public)\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)\s*:\s*array\s*\{(.*?)\n    \}/s';
        if (!preg_match($pattern, $source, $match)) {
            return [];
        }

        return $this->parseRulesArrayFromSource($match[1]);
    }

    /**
     * Parse a PHP rules array from source code using balanced-bracket parsing.
     * Properly handles regex patterns containing [ ] characters.
     */
    private function parseRulesArrayFromSource(string $body): array
    {
        $rules = [];
        $offset = 0;

        while (preg_match("/['\"]([\w.*]+)['\"]\s*=>\s*/", $body, $keyMatch, PREG_OFFSET_CAPTURE, $offset)) {
            $field      = $keyMatch[1][0];
            $valueStart = $keyMatch[0][1] + strlen($keyMatch[0][0]);
            $offset     = $valueStart;

            if ($valueStart >= strlen($body)) break;
            $char = $body[$valueStart];

            if ($char === '[') {
                // Array value — use balanced bracket extraction
                $arrayContent = $this->extractBalancedBrackets($body, $valueStart);
                if ($arrayContent === null) continue;

                $offset = $valueStart + strlen($arrayContent) + 2;

                // Extract string rules
                preg_match_all("/['\"]([^'\"]+)['\"]/", $arrayContent, $ruleItems);
                $ruleStrings = $ruleItems[1] ?? [];

                // Filter out regex patterns
                $ruleStrings = array_values(array_filter($ruleStrings, function ($r) {
                    return !preg_match('#^/.+/[a-z]*$#i', $r);
                }));

                // Detect Rule::enum()
                if (preg_match('/Rule::enum\s*\(\s*([A-Za-z\\\\]+)::class\s*\)/', $arrayContent, $enumMatch)) {
                    $enumClass = $this->resolveEnumClass($enumMatch[1], $body);
                    if ($enumClass && enum_exists($enumClass)) {
                        $cases = array_column($enumClass::cases(), 'value');
                        $ruleStrings[] = 'in:' . implode(',', $cases);
                    } else {
                        $ruleStrings[] = 'enum';
                    }
                }

                if (preg_match('/Rule::unique\s*\(/', $arrayContent)) {
                    $ruleStrings[] = 'unique';
                }
                if (preg_match('/Rule::requiredIf\s*\(/', $arrayContent)) {
                    $ruleStrings[] = 'required_if';
                }

                if (!empty($ruleStrings)) {
                    $rules[$field] = implode('|', $ruleStrings);
                }
            } elseif ($char === "'" || $char === '"') {
                $quote = $char;
                if (preg_match('/' . $quote . '([^' . $quote . ']+)' . $quote . '/', substr($body, $valueStart), $strMatch)) {
                    $rules[$field] = $strMatch[1];
                }
            }
        }

        return $rules;
    }

    /**
     * Extract content between balanced brackets [ ... ]
     * Handles nested brackets, strings, and regex patterns inside strings.
     */
    private function extractBalancedBrackets(string $source, int $startPos): ?string
    {
        if ($source[$startPos] !== '[') return null;

        $depth   = 0;
        $len     = strlen($source);
        $inStr   = false;
        $strChar = '';

        for ($i = $startPos; $i < $len; $i++) {
            $ch = $source[$i];

            if (!$inStr && ($ch === "'" || $ch === '"')) {
                $inStr   = true;
                $strChar = $ch;
                continue;
            }
            if ($inStr && $ch === $strChar && ($i === 0 || $source[$i - 1] !== '\\')) {
                $inStr = false;
                continue;
            }
            if ($inStr) continue;

            if ($ch === '[') $depth++;
            if ($ch === ']') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $startPos + 1, $i - $startPos - 1);
                }
            }
        }

        return null;
    }

    /**
     * Try to resolve short enum class name to FQCN.
     */
    private function resolveEnumClass(string $shortName, string $body): ?string
    {
        if (enum_exists($shortName)) {
            return $shortName;
        }

        $namespaces = [
            'Modules\\Schemes\\Enums\\',
            'Modules\\Enrollments\\Enums\\',
            'Modules\\Learning\\Enums\\',
            'Modules\\Grading\\Enums\\',
            'Modules\\Gamification\\Enums\\',
            'Modules\\Auth\\Enums\\',
            'Modules\\Forums\\Enums\\',
            'Modules\\Content\\Enums\\',
        ];

        foreach ($namespaces as $ns) {
            $fqcn = $ns . $shortName;
            if (enum_exists($fqcn)) {
                return $fqcn;
            }
        }

        return null;
    }

    private function rulesFromValidateCall(string $className, string $methodName): array
    {
        try {
            $ref   = new ReflectionMethod($className, $methodName);
            $file  = $ref->getFileName();
            $start = $ref->getStartLine();
            $end   = $ref->getEndLine();

            if (!$file) return [];

            $lines = file($file);
            $body  = implode('', array_slice($lines, $start - 1, $end - $start + 1));

            if (preg_match('/->validate\s*\(\s*\[(.+?)\]\s*[,\)]/s', $body, $matches)) {
                return $this->parseRulesFromString('[' . $matches[1] . ']');
            }
        } catch (\Throwable) {
        }

        return [];
    }

    private function parseRulesFromString(string $rulesString): array
    {
        $rules = [];
        preg_match_all(
            "/['\"]([\w.]+)['\"]\s*=>\s*(?:['\"]([^'\"]+)['\"]|\[([^\]]+)\])/",
            $rulesString,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $field = $match[1];
            if (!empty($match[2])) {
                $rules[$field] = $match[2];
            } elseif (!empty($match[3])) {
                preg_match_all("/['\"]([^'\"]+)['\"]/", $match[3], $ruleItems);
                $rules[$field] = implode('|', $ruleItems[1]);
            }
        }

        return $rules;
    }

    private function normalizeRules(array $raw): array
    {
        $normalized = [];
        foreach ($raw as $field => $rule) {
            if (is_array($rule)) {
                $parts = [];
                foreach ($rule as $r) {
                    if (is_string($r)) {
                        $parts[] = $r;
                    } elseif ($r instanceof EnumRule) {
                        try {
                            $ref = new \ReflectionProperty($r, 'type');
                            $ref->setAccessible(true);
                            $enumClass = $ref->getValue($r);
                            if (enum_exists($enumClass)) {
                                $cases = array_column($enumClass::cases(), 'value');
                                $parts[] = 'in:' . implode(',', $cases);
                            } else {
                                $parts[] = 'enum';
                            }
                        } catch (\Throwable) {
                            $parts[] = (string) $r;
                        }
                    } elseif (is_object($r)) {
                        $str = (string) $r;
                        $parts[] = $str ?: get_class($r);
                    }
                }
                $normalized[$field] = implode('|', $parts);
            } else {
                $normalized[$field] = $rule;
            }
        }
        return $normalized;
    }

    // ──────────────────────────────────────────────
    // Build valid body from rules
    // ──────────────────────────────────────────────
    private function buildValidBody(array $rules): array
    {
        $body = [];
        foreach ($rules as $field => $ruleString) {
            // Skip nested array wildcard fields
            if (str_contains($field, '.*')) {
                continue;
            }

            if (str_contains($ruleString, 'nullable') && !str_contains($ruleString, 'required')) {
                continue;
            }

            $body[$field] = $this->guessValidValue($field, $ruleString);
        }
        return $body;
    }

    private function guessValidValue(string $field, string $ruleString): mixed
    {
        // 1. Exact match from dictionary (highest priority)
        if (isset($this->validValues[$field])) {
            return $this->validValues[$field];
        }

        // 2. Check 'in:' rule — use first valid option
        if (preg_match('/in:([^\|]+)/', $ruleString, $m)) {
            $options = explode(',', $m[1]);
            return trim($options[0]);
        }

        // 3. Partial match (e.g., "new_email" -> email)
        foreach ($this->validValues as $key => $val) {
            if (strlen($key) >= 3 && str_contains($field, $key) && $val !== null) {
                return $val;
            }
        }

        // 4. Infer from rule types
        if (str_contains($ruleString, 'integer')) return 1;
        if (str_contains($ruleString, 'numeric')) return 1.0;
        if (str_contains($ruleString, 'email'))   return 'test@levl.test';
        if (str_contains($ruleString, 'boolean'))  return true;
        if (str_contains($ruleString, 'array'))    return [];
        if (str_contains($ruleString, 'date')) {
            if (str_contains($ruleString, 'after:now')) {
                return now()->addDay()->format('Y-m-d H:i:s');
            }
            return now()->toDateString();
        }
        if (str_contains($ruleString, 'url'))      return 'https://levl.test';
        if (str_contains($ruleString, 'uuid'))     return '550e8400-e29b-41d4-a716-446655440000';
        if (str_contains($ruleString, 'json'))     return '{}';
        if (str_contains($ruleString, 'exists:'))  return 1;

        if (str_contains($ruleString, 'image') || str_contains($ruleString, 'file') || str_contains($ruleString, 'mimes')) {
            return null;
        }

        // 5. min:N for strings
        if (preg_match('/min:(\d+)/', $ruleString, $m)) {
            return str_repeat('a', max((int) $m[1], 3));
        }

        return 'test_value';
    }

    // ──────────────────────────────────────────────
    // Build invalid test cases
    // ──────────────────────────────────────────────
    private function buildInvalidCases(array $rules): array
    {
        $cases = [];

        foreach ($rules as $field => $ruleString) {
            if (str_contains($field, '.*')) continue;

            $validBody = $this->buildValidBody($rules);

            // Required field missing
            if (str_contains($ruleString, 'required') && !str_contains($ruleString, 'required_if') && !str_contains($ruleString, 'required_with')) {
                $body = $validBody;
                unset($body[$field]);
                $cases[] = [
                    'scenario'        => "Field '{$field}' tidak diisi (wajib diisi)",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Wrong type
            if (str_contains($ruleString, 'integer') || str_contains($ruleString, 'numeric')) {
                $body         = $validBody;
                $body[$field] = 'bukan_angka';
                $cases[] = [
                    'scenario'        => "Field '{$field}' diisi string (harus angka)",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Invalid email
            if (str_contains($ruleString, 'email')) {
                $body         = $validBody;
                $body[$field] = 'bukan_email_valid';
                $cases[] = [
                    'scenario'        => "Field '{$field}' diisi email tidak valid",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Below minimum
            if (preg_match('/min:(\d+)/', $ruleString, $m)) {
                $minVal       = (int) $m[1];
                $body         = $validBody;
                if (str_contains($ruleString, 'integer') || str_contains($ruleString, 'numeric')) {
                    $body[$field] = max(0, $minVal - 1);
                } else {
                    $body[$field] = $minVal > 1 ? str_repeat('a', $minVal - 1) : '';
                }
                $cases[] = [
                    'scenario'        => "Field '{$field}' di bawah minimum ({$minVal})",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Above maximum
            if (preg_match('/max:(\d+)/', $ruleString, $m)) {
                $maxVal       = (int) $m[1];
                $body         = $validBody;
                if (str_contains($ruleString, 'integer') || str_contains($ruleString, 'numeric')) {
                    $body[$field] = $maxVal + 999;
                } else {
                    $body[$field] = str_repeat('a', $maxVal + 10);
                }
                $cases[] = [
                    'scenario'        => "Field '{$field}' melebihi maksimum ({$maxVal})",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Invalid enum / in: value
            if (preg_match('/in:([^\|]+)/', $ruleString, $m)) {
                $body         = $validBody;
                $body[$field] = 'nilai_tidak_valid_xyz';
                $cases[] = [
                    'scenario'        => "Field '{$field}' diisi nilai di luar opsi yang valid",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }

            // Unique constraint
            if (str_contains($ruleString, 'unique')) {
                $cases[] = [
                    'scenario'        => "Field '{$field}' menggunakan nilai yang sudah ada (unique constraint)",
                    'body'            => $validBody,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                    'note'            => 'Perlu data existing di database untuk test ini',
                ];
            }

            // Invalid UUID
            if (str_contains($ruleString, 'uuid')) {
                $body         = $validBody;
                $body[$field] = 'bukan-uuid-valid';
                $cases[] = [
                    'scenario'        => "Field '{$field}' diisi format UUID tidak valid",
                    'body'            => $body,
                    'expected_status' => 422,
                    'expected_error'  => $field,
                ];
            }
        }

        // All required fields empty
        $requiredFields = array_filter($rules, fn ($r) => str_contains($r, 'required'));
        if (!empty($requiredFields)) {
            $cases[] = [
                'scenario'        => 'Semua field wajib dikosongkan',
                'body'            => [],
                'expected_status' => 422,
                'expected_error'  => array_key_first($requiredFields),
            ];
        }

        return $cases;
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────
    private function extractPathParams(string $uri): array
    {
        preg_match_all('/\{(\w+?)(?::[\w]+)?\}/', $uri, $matches);
        return $matches[1] ?? [];
    }

    private function requiresAuth(array $middlewares): bool
    {
        foreach ($middlewares as $mw) {
            if (str_contains($mw, 'auth:api') || str_contains($mw, 'auth:sanctum')) {
                return true;
            }
        }
        return false;
    }

    private function extractRoles(array $middlewares): array
    {
        $roles = [];
        foreach ($middlewares as $mw) {
            if (preg_match('/^role:(.+)$/', $mw, $m)) {
                $roleStr = $m[1];
                $parsed  = preg_split('/[|,]/', $roleStr);
                $roles   = array_merge($roles, array_map('trim', $parsed));
            }
        }
        return array_unique($roles);
    }
}