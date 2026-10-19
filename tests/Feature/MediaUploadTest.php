<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_image()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/media/upload', [
            'image' => UploadedFile::fake()->image('test.jpg', 100, 100)
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'url', 'path']);

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_requires_authentication()
    {
        $response = $this->postJson('/api/media/upload', [
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg')
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_requires_valid_image()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/media/upload', [
            'image' => 'not-a-file'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_upload_large_webp_image()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // 5MB WEBP file (5120 KB)
        $file = UploadedFile::fake()->create('sample.webp', 5120, 'image/webp');

        $response = $this->actingAs($user)->postJson('/api/media/upload', [
            'image' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'url', 'path']);

        $path = $response->json('path');
        Storage::disk('public')->assertExists($path);
    }
}
