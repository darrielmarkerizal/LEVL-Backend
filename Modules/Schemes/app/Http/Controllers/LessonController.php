<?php

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Schemes\Http\Requests\LessonRequest;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Unit;
use Modules\Schemes\Services\LessonService;

class LessonController extends Controller
{
    use ApiResponse;

    public function __construct(private LessonService $service) {}

    public function index(Request $request, int $course, int $unit)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $unitModel = Unit::find($unit);
        if (! $unitModel || (int) $unitModel->course_id !== $course) {
            return $this->error('Unit tidak ditemukan di course ini.', 404);
        }

        $courseModel = Course::find($course);
        if (! $courseModel) {
            return $this->error('Course tidak ditemukan.', 404);
        }

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $courseModel->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($courseModel, 'hasAdmin') && $courseModel->hasAdmin($user)) {
                $authorized = true;
            }
        } elseif ($user->hasRole('student')) {
            $enrollment = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
            if ($enrollment) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda tidak memiliki akses untuk melihat lessons di course ini.', 403);
        }

        $params = $request->all();
        $paginator = $this->service->listByUnit($unit, $params);

        return $this->paginateResponse($paginator);
    }

    public function store(LessonRequest $request, int $course, int $unit)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $unitModel = Unit::find($unit);
        if (! $unitModel || (int) $unitModel->course_id !== $course) {
            return $this->error('Unit tidak ditemukan di course ini.', 404);
        }

        $courseModel = Course::find($course);
        if (! $courseModel) {
            return $this->error('Course tidak ditemukan.', 404);
        }

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $courseModel->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($courseModel, 'hasAdmin') && $courseModel->hasAdmin($user)) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda hanya dapat membuat lesson untuk course yang Anda buat atau course yang Anda kelola sebagai admin.', 403);
        }

        $data = $request->validated();
        $lesson = $this->service->create($unit, $data);

        return $this->created(['lesson' => $lesson], 'Lesson berhasil dibuat.');
    }

    public function show(int $course, int $unit, int $lesson)
    {
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $unitModel = Unit::find($unit);
        if (! $unitModel || (int) $unitModel->course_id !== $course) {
            return $this->error('Unit tidak ditemukan di course ini.', 404);
        }

        $found = $this->service->show($unit, $lesson);
        if (! $found) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }

        $courseModel = Course::find($course);
        if (! $courseModel) {
            return $this->error('Course tidak ditemukan.', 404);
        }

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $courseModel->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($courseModel, 'hasAdmin') && $courseModel->hasAdmin($user)) {
                $authorized = true;
            }
        } elseif ($user->hasRole('student')) {
            $enrollment = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
            if ($enrollment) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda tidak memiliki akses untuk melihat lesson ini.', 403);
        }

        return $this->success(['lesson' => $found]);
    }

    public function update(LessonRequest $request, int $course, int $unit, int $lesson)
    {
        $found = $this->service->show($unit, $lesson);
        if (! $found) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('update', $found)) {
            return $this->error('Anda tidak memiliki akses untuk mengubah lesson ini.', 403);
        }

        $data = $request->validated();
        $updated = $this->service->update($unit, $lesson, $data);

        return $this->success(['lesson' => $updated], 'Lesson berhasil diperbarui.');
    }

    public function destroy(int $course, int $unit, int $lesson)
    {
        $found = $this->service->show($unit, $lesson);
        if (! $found) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('delete', $found)) {
            return $this->error('Anda tidak memiliki akses untuk menghapus lesson ini.', 403);
        }

        $ok = $this->service->delete($unit, $lesson);

        return $this->success([], 'Lesson berhasil dihapus.');
    }

    public function publish(int $course, int $unit, int $lesson)
    {
        $found = $this->service->show($unit, $lesson);
        if (! $found) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('update', $found)) {
            return $this->error('Anda tidak memiliki akses untuk mempublish lesson ini.', 403);
        }

        $updated = $this->service->publish($unit, $lesson);

        return $this->success(['lesson' => $updated], 'Lesson berhasil dipublish.');
    }

    public function unpublish(int $course, int $unit, int $lesson)
    {
        $found = $this->service->show($unit, $lesson);
        if (! $found) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('update', $found)) {
            return $this->error('Anda tidak memiliki akses untuk unpublish lesson ini.', 403);
        }

        $updated = $this->service->unpublish($unit, $lesson);

        return $this->success(['lesson' => $updated], 'Lesson berhasil diunpublish.');
    }
}
