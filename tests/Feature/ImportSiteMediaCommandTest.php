<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Services\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportSiteMediaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_create_uploads(): void
    {
        $post = Post::factory()->create();
        $post->addMedia(UploadedFile::fake()->image('existing.jpg', 1200, 675))
            ->toMediaCollection('featured-image');

        $this->artisan('site-media:import', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Would import 1 new upload(s).');

        $this->assertCount(0, app(SiteMedia::class)->items());
    }

    public function test_import_command_adds_unique_post_images_to_uploads(): void
    {
        $post = Post::factory()->create();
        $post->addMedia(UploadedFile::fake()->image('existing.jpg', 1200, 675))
            ->toMediaCollection('featured-image');

        $this->artisan('site-media:import')
            ->assertSuccessful()
            ->expectsOutputToContain('Imported 1 new upload(s).');

        $this->assertCount(1, app(SiteMedia::class)->items());
    }
}
