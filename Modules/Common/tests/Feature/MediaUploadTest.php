<?php

declare(strict_types=1);

namespace Modules\Common\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_authenticated_user_can_upload_media(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->postJson(route('media.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'file_name',
                    'mime_type',
                    'size',
                    'url',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('temporary_media', [
            'user_id' => $user->id,
        ]);
        
        $this->assertDatabaseHas('media', [
            'model_type' => 'Modules\Common\Models\TemporaryMedia',
            'collection_name' => 'globalmedia',
        ]);
    }

    public function test_unauthenticated_user_cannot_upload_media(): void
    {
        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->postJson(route('media.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_fails_without_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->postJson(route('media.upload'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_with_invalid_mime_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $file = UploadedFile::fake()->create('test.exe', 100);

        $response = $this->postJson(route('media.upload'), [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}
