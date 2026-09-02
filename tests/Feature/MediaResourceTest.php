<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use App\Services\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class MediaResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        SiteMedia::resetLibrarySyncState();
        $this->admin = User::factory()->create();
    }

    public function test_list_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListMedia::class)
            ->assertSuccessful();
    }

    public function test_list_page_imports_post_featured_images_into_uploads(): void
    {
        $this->actingAs($this->admin);

        Site::instance()
            ->addMedia(UploadedFile::fake()->image('site.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $post = Post::factory()->create();
        $post->addMedia(UploadedFile::fake()->image('post.jpg', 900, 500))
            ->toMediaCollection('featured-image');

        Livewire::test(ListMedia::class)
            ->assertSuccessful();

        $uploads = app(SiteMedia::class)->items();

        $this->assertCount(2, $uploads);
        $this->assertTrue($uploads->contains('file_name', 'site.jpg'));
        $this->assertTrue($uploads->contains('file_name', 'post.jpg'));
    }
}
