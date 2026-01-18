<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DevController extends Controller
{
    public function checkOctane(Request $request)
    {
        $isOctane = isset($_SERVER['LARAVEL_OCTANE']);
        
        $data = [
            'is_octane' => $isOctane,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => phpversion(),
            'octane_server' => $_SERVER['LARAVEL_OCTANE'] ?? 'N/A',
            'pid' => getmypid(),
            'memory_usage' => memory_get_usage(true),
            'environment' => app()->environment(),
        ];

        return view('dev.octane-check', $data);
    }

    public function benchmarkView()
    {
        return view('dev.benchmark');
    }

    public function benchmarkApi(Request $request)
    {
        $startTime = microtime(true);
        
        $mode = $request->query('mode', 'simple');
        
        $result = match($mode) {
            // Production API Benchmarks (using HTTP client for realistic testing)
            'auth_login' => $this->benchmarkAuthLogin(),
            'auth_profile' => $this->benchmarkAuthProfile(),
            'courses_list' => $this->benchmarkCoursesList(),
            'courses_detail' => $this->benchmarkCoursesDetail(),
            'users_list' => $this->benchmarkUsersList(),
            
            // Database Query Benchmarks (direct DB queries)
            'dashboard' => $this->benchmarkStudentDashboard(),
            'courses' => $this->benchmarkCourseList(),
            'enrollment' => $this->benchmarkEnrollmentCheck(),
            'db' => \DB::select('select 1'),
            
            // Simple response (no DB)
            default => ['simple' => true],
        };

        $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        return response()->json([
            'status' => 'ok',
            'mode' => $mode,
            'result' => 1,
            'response_time_ms' => round($responseTime, 2),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_human' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => memory_get_peak_usage(true),
            'cpu_load' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'pid' => getmypid(),
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Benchmark: Student Dashboard Query
     * Simulates getting enrolled courses with progress for a student
     */
    private function benchmarkStudentDashboard()
    {
        // Get a random user ID (simulate different students)
        $userId = \DB::table('users')
            ->where('deleted_at', null)
            ->inRandomOrder()
            ->value('id') ?? 1;

        // Realistic dashboard query: enrolled courses with units count
        return \DB::table('enrollments as e')
            ->join('courses as c', 'c.id', '=', 'e.course_id')
            ->leftJoin('units as u', 'u.course_id', '=', 'c.id')
            ->leftJoin('categories as cat', 'cat.id', '=', 'c.category_id')
            ->where('e.user_id', $userId)
            ->whereNull('c.deleted_at')
            ->select(
                'c.id',
                'c.title',
                'c.code',
                'c.status',
                'cat.name as category_name',
                \DB::raw('COUNT(DISTINCT u.id) as total_units'),
                'e.status as enrollment_status',
                'e.enrolled_at'
            )
            ->groupBy('c.id', 'c.title', 'c.code', 'c.status', 'cat.name', 'e.status', 'e.enrolled_at')
            ->limit(10)
            ->get();
    }

    /**
     * Benchmark: Course Listing Query
     * Simulates browsing published courses with category filter
     */
    private function benchmarkCourseList()
    {
        return \DB::table('courses as c')
            ->leftJoin('categories as cat', 'cat.id', '=', 'c.category_id')
            ->leftJoin('users as instructor', 'instructor.id', '=', 'c.instructor_id')
            ->whereNull('c.deleted_at')
            ->where('c.status', 'published')
            ->select(
                'c.id',
                'c.title',
                'c.code',
                'c.short_desc',
                'c.level_tag',
                'c.duration_estimate',
                'cat.name as category_name',
                'instructor.name as instructor_name',
                'c.published_at'
            )
            ->orderBy('c.published_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Benchmark: Enrollment Check Query
     * Simulates checking if user is enrolled in a course
     */
    private function benchmarkEnrollmentCheck()
    {
        $userId = \DB::table('users')
            ->where('deleted_at', null)
            ->inRandomOrder()
            ->value('id') ?? 1;

        $courseId = \DB::table('courses')
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->value('id') ?? 1;

        return \DB::table('enrollments')
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }

    /**
     * Benchmark: Auth Login (Production API)
     * Tests the full login flow including validation, authentication, and token generation
     */
    private function benchmarkAuthLogin()
    {
        try {
            $response = \Http::post(url('/api/v1/auth/login'), [
                'login' => 'admin@example.com',
                'password' => 'password',
            ]);
            
            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Benchmark: Auth Profile (Production API)
     * Tests authenticated request with JWT token
     */
    private function benchmarkAuthProfile()
    {
        try {
            // Get a valid token first (cached for performance)
            static $token = null;
            if (!$token) {
                $loginResponse = \Http::post(url('/api/v1/auth/login'), [
                    'login' => 'admin@example.com',
                    'password' => 'password',
                ]);
                $token = $loginResponse->json('data.token');
            }

            $response = \Http::withToken($token)->get(url('/api/v1/auth/me'));
            
            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Benchmark: Courses List (Production API)
     * Tests the courses listing endpoint with pagination
     */
    private function benchmarkCoursesList()
    {
        try {
            $response = \Http::get(url('/api/v1/courses'), [
                'per_page' => 15,
            ]);
            
            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Benchmark: Courses Detail (Production API)
     * Tests getting a single course detail
     */
    private function benchmarkCoursesDetail()
    {
        try {
            // Get a random course slug
            $slug = \DB::table('courses')
                ->whereNull('deleted_at')
                ->where('status', 'published')
                ->inRandomOrder()
                ->value('slug');

            if (!$slug) {
                return ['error' => 'No published courses found'];
            }

            $response = \Http::get(url("/api/v1/courses/{$slug}"));
            
            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Benchmark: Users List (Production API)
     * Tests the users listing endpoint (admin only)
     */
    private function benchmarkUsersList()
    {
        try {
            // Get admin token (cached)
            static $token = null;
            if (!$token) {
                $loginResponse = \Http::post(url('/api/v1/auth/login'), [
                    'login' => 'admin@example.com',
                    'password' => 'password',
                ]);
                $token = $loginResponse->json('data.token');
            }

            $response = \Http::withToken($token)->get(url('/api/v1/users'), [
                'per_page' => 15,
            ]);
            
            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
