<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Trash\Models\TrashBin;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    createTestRoles();
});

function createTrashUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createQuizForUnit(Unit $unit, User $creator): Quiz
{
    return Quiz::query()->create([
        'unit_id' => $unit->id,
        'order' => 1,
        'created_by' => $creator->id,
        'title' => 'Unit quiz',
        'description' => 'Quiz for restore tests',
        'passing_grade' => 75,
        'auto_grading' => true,
        'max_score' => 100,
        'time_limit_minutes' => 30,
        'randomization_type' => 'static',
        'review_mode' => 'immediate',
        'status' => 'published',
    ]);
}

test('restoring a course does not restore descendants automatically', function () {
    $admin = createTrashUser('Admin');

    $course = Course::factory()->create([
        'instructor_id' => $admin->id,
        'status' => 'published',
    ]);
    $unit = Unit::factory()->forCourse($course)->create(['status' => 'published']);
    $lesson = Lesson::factory()->forUnit($unit)->create(['status' => 'published']);
    $quiz = createQuizForUnit($unit, $admin);
    $assignment = Assignment::factory()->create([
        'unit_id' => $unit->id,
        'created_by' => $admin->id,
        'status' => 'published',
    ]);

    $this->actingAs($admin, 'api');
    $course->delete();

    expect(Course::withTrashed()->findOrFail($course->id)->trashed())->toBeTrue();
    expect(Unit::withTrashed()->findOrFail($unit->id)->trashed())->toBeTrue();
    expect(Lesson::withTrashed()->findOrFail($lesson->id)->trashed())->toBeTrue();
    expect(Quiz::withTrashed()->findOrFail($quiz->id)->trashed())->toBeTrue();
    expect(Assignment::withTrashed()->findOrFail($assignment->id)->trashed())->toBeTrue();

    $courseBin = TrashBin::query()
        ->where('trashable_type', Course::class)
        ->where('trashable_id', $course->id)
        ->firstOrFail();

    $this->patchJson(api('/trash-bins/'.$courseBin->id))
        ->assertOk();

    expect(Course::withTrashed()->findOrFail($course->id)->trashed())->toBeFalse();
    expect(Unit::withTrashed()->findOrFail($unit->id)->trashed())->toBeTrue();
    expect(Lesson::withTrashed()->findOrFail($lesson->id)->trashed())->toBeTrue();
    expect(Quiz::withTrashed()->findOrFail($quiz->id)->trashed())->toBeTrue();
    expect(Assignment::withTrashed()->findOrFail($assignment->id)->trashed())->toBeTrue();

    assertDatabaseMissing('trash_bins', [
        'trashable_type' => Course::class,
        'trashable_id' => $course->id,
    ]);

    assertDatabaseHas('trash_bins', [
        'trashable_type' => Unit::class,
        'trashable_id' => $unit->id,
    ]);
    assertDatabaseHas('trash_bins', [
        'trashable_type' => Lesson::class,
        'trashable_id' => $lesson->id,
    ]);
    assertDatabaseHas('trash_bins', [
        'trashable_type' => Quiz::class,
        'trashable_id' => $quiz->id,
    ]);
    assertDatabaseHas('trash_bins', [
        'trashable_type' => Assignment::class,
        'trashable_id' => $assignment->id,
    ]);
});

test('instructor can restore trash in a course they are responsible for even if deleted by another user', function () {
    $instructor = createTrashUser('Instructor');
    $admin = createTrashUser('Admin');

    $course = Course::factory()->create([
        'instructor_id' => $instructor->id,
    ]);
    $unit = Unit::factory()->forCourse($course)->create();

    $this->actingAs($admin, 'api');
    $unit->delete();

    $unitBin = TrashBin::query()
        ->where('trashable_type', Unit::class)
        ->where('trashable_id', $unit->id)
        ->firstOrFail();

    $this->actingAs($instructor, 'api')
        ->patchJson(api('/trash-bins/'.$unitBin->id))
        ->assertOk();

    expect(Unit::withTrashed()->findOrFail($unit->id)->trashed())->toBeFalse();
    assertDatabaseMissing('trash_bins', [
        'trashable_type' => Unit::class,
        'trashable_id' => $unit->id,
    ]);
});

test('admin can permanently erase trash they deleted themselves even without course responsibility', function () {
    $admin = createTrashUser('Admin');
    $instructor = createTrashUser('Instructor');

    $course = Course::factory()->create([
        'instructor_id' => $instructor->id,
    ]);
    $unit = Unit::factory()->forCourse($course)->create();
    $lesson = Lesson::factory()->forUnit($unit)->create();

    $this->actingAs($admin, 'api');
    $lesson->delete();

    $lessonBin = TrashBin::query()
        ->where('trashable_type', Lesson::class)
        ->where('trashable_id', $lesson->id)
        ->firstOrFail();

    $this->deleteJson(api('/trash-bins/'.$lessonBin->id))
        ->assertOk();

    expect(Lesson::withTrashed()->find($lesson->id))->toBeNull();
    assertDatabaseMissing('trash_bins', [
        'trashable_type' => Lesson::class,
        'trashable_id' => $lesson->id,
    ]);
});

test('instructor cannot restore trash outside their own deletes and unmanaged courses', function () {
    $actor = createTrashUser('Instructor');
    $courseInstructor = createTrashUser('Instructor');
    $admin = createTrashUser('Admin');

    $course = Course::factory()->create([
        'instructor_id' => $courseInstructor->id,
    ]);
    $unit = Unit::factory()->forCourse($course)->create();

    $this->actingAs($admin, 'api');
    $unit->delete();

    $unitBin = TrashBin::query()
        ->where('trashable_type', Unit::class)
        ->where('trashable_id', $unit->id)
        ->firstOrFail();

    $this->actingAs($actor, 'api')
        ->patchJson(api('/trash-bins/'.$unitBin->id))
        ->assertStatus(403);

    expect(Unit::withTrashed()->findOrFail($unit->id)->trashed())->toBeTrue();
    assertDatabaseHas('trash_bins', [
        'trashable_type' => Unit::class,
        'trashable_id' => $unit->id,
    ]);
});
