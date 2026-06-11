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

        $this->admin = User::factory()->create();
    }

    public function test_list_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListMedia::class)
            ->assertSuccessful();
    }

    public function test_list_page_only_shows_site_uploads(): void
    {
        $this->actingAs($this->admin);

        $siteUpload = Site::instance()
            ->addMedia(UploadedFile::fake()->image('site.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $post = Post::factory()->create();
        $post->addMedia(UploadedFile::fake()->image('post.jpg', 1200, 675))
            ->toMediaCollection('featured-image');

        Livewire::test(ListMedia::class)
            ->assertCanSeeTableRecords([$siteUpload])
            ->assertCanNotSeeTableRecords([$post->getFirstMedia('featured-image')]);
    }
}
