<?php

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Modules\Enrollments\DTOs\CreateEnrollmentDTO;
use Modules\Enrollments\Events\EnrollmentCreated;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Services\EnrollmentService;
use Modules\Schemes\Models\Course;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(EnrollmentService::class);
    Mail::fake();
    Event::fake();
});

test('enroll creates active enrollment for auto accept course', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create(['enrollment_type' => 'auto_accept']);

    $dto = CreateEnrollmentDTO::fromRequest(['course_id' => $course->id]);
    $result = $this->service->enroll($course, $user, $dto);

    expect($result['status'])->toEqual('active');
    assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);
});

test('enroll creates pending enrollment for approval course', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create(['enrollment_type' => 'approval']);

    $dto = CreateEnrollmentDTO::fromRequest(['course_id' => $course->id]);
    $result = $this->service->enroll($course, $user, $dto);

    expect($result['status'])->toEqual('pending');
    assertDatabaseHas('enrollments', [
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);
});

test('enroll validates enrollment key for key based course', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $keyHasher = app(\App\Contracts\EnrollmentKeyHasherInterface::class);
    $course = Course::factory()->create([
        'enrollment_type' => 'key_based',
        'enrollment_key_hash' => $keyHasher->hash('secret-key-123'),
    ]);

    $dto = CreateEnrollmentDTO::fromRequest([
        'course_id' => $course->id,
        'enrollment_key' => 'wrong-key',
    ]);

    expect(fn () => $this->service->enroll($course, $user, $dto))
        ->toThrow(BusinessException::class);
});

test('enroll creates active enrollment with correct key', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $keyHasher = app(\App\Contracts\EnrollmentKeyHasherInterface::class);
    $course = Course::factory()->create([
        'enrollment_type' => 'key_based',
        'enrollment_key_hash' => $keyHasher->hash('secret-key-123'),
    ]);

    $dto = CreateEnrollmentDTO::fromRequest([
        'course_id' => $course->id,
        'enrollment_key' => 'secret-key-123',
    ]);
    $result = $this->service->enroll($course, $user, $dto);

    expect($result['status'])->toEqual('active');
});

test('enroll dispatches enrollment created event', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create(['enrollment_type' => 'auto_accept']);

    $dto = CreateEnrollmentDTO::fromRequest(['course_id' => $course->id]);
    $this->service->enroll($course, $user, $dto);

    Event::assertDispatched(EnrollmentCreated::class);
});

test('approve changes pending to active', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    $result = $this->service->approve($enrollment);

    expect($result->status->value)->toEqual('active');
    expect($result->enrolled_at)->not->toBeNull();
});

test('approve throws exception for non pending enrollment', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect(fn () => $this->service->approve($enrollment))
        ->toThrow(BusinessException::class);
});

test('decline changes pending to cancelled', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    $result = $this->service->decline($enrollment);

    expect($result->status->value)->toEqual('cancelled');
});

test('cancel changes pending to cancelled', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    $result = $this->service->cancel($enrollment);

    expect($result->status->value)->toEqual('cancelled');
});

test('cancel throws exception for non pending enrollment', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    expect(fn () => $this->service->cancel($enrollment))
        ->toThrow(BusinessException::class);
});

test('withdraw changes active to cancelled', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'active',
    ]);

    $result = $this->service->withdraw($enrollment);

    expect($result->status->value)->toEqual('cancelled');
});

test('withdraw throws exception for non active enrollment', function () {
    $user = \Modules\Auth\Models\User::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'status' => 'pending',
    ]);

    expect(fn () => $this->service->withdraw($enrollment))
        ->toThrow(BusinessException::class);
});
