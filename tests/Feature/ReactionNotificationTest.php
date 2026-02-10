<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Auth\Models\User;
use Modules\Forums\Models\Thread;
use Modules\Forums\Models\Reply;
use Modules\Notifications\Services\NotificationService;
use Modules\Schemes\Models\Course;
use Tests\TestCase;
use Mockery;

class ReactionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calls_notification_service_with_correct_arguments_and_persists_to_db()
    {
        // Mock the NotificationService
        $notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->app->instance(NotificationService::class, $notificationServiceMock);

        // Expect the send method to be called
        $notificationServiceMock->shouldReceive('send')
            ->once()
            ->withArgs(function ($userIdOrDto, $type, $title, $message, $data) {
                return $type === 'forum_reaction_thread';
            });

        // Setup data
        $user = User::factory()->create();
        $author = User::factory()->create();
        $course = Course::factory()->create();
        $thread = Thread::factory()->create([
            'author_id' => $author->id,
            'course_id' => $course->id,
        ]);

        // Act: Trigger the reaction
        $response = $this->actingAs($user)
            ->postJson(route('api.courses.forum.threads.reactions.store', [
                'course' => $course->slug,
                'thread' => $thread->id,
            ]), [
                'type' => 'like',
            ]);

        // Assert
        $response->assertStatus(200);
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.id'));
        $this->assertEquals('like', $response->json('data.type'));
        
        // Additional assertion: Check if reaction is in DB
        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_id' => $thread->id,
            'reactable_type' => Thread::class,
            'type' => 'like',
        ]);
    }
}
