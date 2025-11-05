<?php

namespace Modules\Schemes\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Modules\Schemes\Http\Requests\LessonBlockRequest;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\LessonBlock;
use Modules\Schemes\Services\LessonBlockService;

class LessonBlockController extends Controller
{
    use ApiResponse;

    public function __construct(private LessonBlockService $service) {}

    public function index(int $course, int $unit, int $lesson)
    {
        $lessonModel = Lesson::with(['unit.course'])->find($lesson);
        if (! $lessonModel) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }
        if ((int) ($lessonModel->unit?->course_id) !== $course || (int) ($lessonModel->unit_id) !== $unit) {
            return $this->error('Lesson tidak ditemukan di course ini.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $course = $lessonModel->unit?->course;
        if (! $course) {
            return $this->error('Course tidak ditemukan.', 404);
        }

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $course->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                $authorized = true;
            }
        } elseif ($user->hasRole('student')) {
            $enrolled = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
            if ($enrolled) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda tidak memiliki akses untuk melihat blok lesson ini.', 403);
        }

        $blocks = $this->service->list($lesson);

        return $this->success(['blocks' => $blocks]);
    }

    public function store(LessonBlockRequest $request, int $course, int $unit, int $lesson)
    {
        $lessonModel = Lesson::with(['unit.course'])->find($lesson);
        if (! $lessonModel) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }
        if ((int) ($lessonModel->unit?->course_id) !== $course || (int) ($lessonModel->unit_id) !== $unit) {
            return $this->error('Lesson tidak ditemukan di course ini.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        $course = $lessonModel->unit?->course;
        if (! $course) {
            return $this->error('Course tidak ditemukan.', 404);
        }

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $course->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda hanya dapat mengelola blok untuk course yang Anda kelola.', 403);
        }

        $data = $request->validated();
        $mediaFile = $request->file('media');
        $block = $this->service->create($lesson, $data, $mediaFile);

        return $this->created(['block' => $block], 'Blok lesson berhasil dibuat.');
    }

    public function show(int $course, int $unit, int $lesson, int $block)
    {
        $lessonModel = Lesson::with(['unit.course'])->find($lesson);
        if (! $lessonModel) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }
        if ((int) ($lessonModel->unit?->course_id) !== $course || (int) ($lessonModel->unit_id) !== $unit) {
            return $this->error('Lesson tidak ditemukan di course ini.', 404);
        }

        $found = LessonBlock::where('lesson_id', $lesson)->find($block);
        if (! $found) {
            return $this->error('Blok lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        $course = $lessonModel->unit?->course;

        $authorized = false;
        if ($user->hasRole('super-admin')) {
            $authorized = true;
        } elseif ($user->hasRole('admin')) {
            if ((int) $course->instructor_id === (int) $user->id) {
                $authorized = true;
            } elseif (method_exists($course, 'hasAdmin') && $course->hasAdmin($user)) {
                $authorized = true;
            }
        } elseif ($user->hasRole('student')) {
            $enrolled = \Modules\Enrollments\Models\Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('status', ['active', 'completed'])
                ->exists();
            if ($enrolled) {
                $authorized = true;
            }
        }

        if (! $authorized) {
            return $this->error('Anda tidak memiliki akses untuk melihat blok lesson ini.', 403);
        }

        return $this->success(['block' => $found]);
    }

    public function update(LessonBlockRequest $request, int $course, int $unit, int $lesson, int $block)
    {
        $lessonModel = Lesson::with(['unit.course'])->find($lesson);
        if (! $lessonModel) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }
        if ((int) ($lessonModel->unit?->course_id) !== $course || (int) ($lessonModel->unit_id) !== $unit) {
            return $this->error('Lesson tidak ditemukan di course ini.', 404);
        }

        $found = LessonBlock::where('lesson_id', $lesson)->find($block);
        if (! $found) {
            return $this->error('Blok lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('update', $lessonModel)) {
            return $this->error('Anda tidak memiliki akses untuk mengubah blok ini.', 403);
        }

        $data = $request->validated();
        $mediaFile = $request->file('media');
        $updated = $this->service->update($lesson, $block, $data, $mediaFile);

        return $this->success(['block' => $updated], 'Blok lesson berhasil diperbarui.');
    }

    public function destroy(int $course, int $unit, int $lesson, int $block)
    {
        $lessonModel = Lesson::with(['unit.course'])->find($lesson);
        if (! $lessonModel) {
            return $this->error('Lesson tidak ditemukan.', 404);
        }
        if ((int) ($lessonModel->unit?->course_id) !== $course || (int) ($lessonModel->unit_id) !== $unit) {
            return $this->error('Lesson tidak ditemukan di course ini.', 404);
        }

        $found = LessonBlock::where('lesson_id', $lesson)->find($block);
        if (! $found) {
            return $this->error('Blok lesson tidak ditemukan.', 404);
        }

        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();
        if (! Gate::forUser($user)->allows('delete', $lessonModel)) {
            return $this->error('Anda tidak memiliki akses untuk menghapus blok ini.', 403);
        }

        $this->service->delete($lesson, $block);

        return $this->success([], 'Blok lesson berhasil dihapus.');
    }
}
