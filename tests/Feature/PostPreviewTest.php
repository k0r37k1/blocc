<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PostPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_post_can_be_previewed_with_valid_signed_url(): void
    {
        $post = Post::factory()->draft()->create([
            'title' => 'Draft Preview Post',
            'body' => '<p>Preview body content.</p>',
        ]);

        $url = $post->previewUrl();

        $this->get($url)
            ->assertOk()
            ->assertSee('Draft Preview Post')
            ->assertSee('Preview body content.')
            ->assertSee(__('Preview — this post is not published yet.'));
    }

    public function test_scheduled_post_can_be_previewed_with_valid_signed_url(): void
    {
        $post = Post::factory()->scheduled()->create([
            'title' => 'Scheduled Preview Post',
        ]);

        $this->get($post->previewUrl())
            ->assertOk()
            ->assertSee('Scheduled Preview Post');
    }

    public function test_preview_requires_valid_signature(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('blog.preview', ['post' => $post->getKey()]))
            ->assertForbidden();
    }

    public function test_preview_rejects_expired_signature(): void
    {
        $post = Post::factory()->draft()->create();

        $url = URL::temporarySignedRoute(
            'blog.preview',
            now()->subMinute(),
            ['post' => $post->getKey()],
        );

        $this->get($url)->assertForbidden();
    }

    public function test_draft_post_is_not_publicly_accessible(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('blog.show', $post->slug))
            ->assertNotFound();
    }

    public function test_edit_page_shows_preview_action_for_drafts(): void
    {
        $admin = User::factory()->create();
        $post = Post::factory()->draft()->create();

        $this->actingAs($admin);

        Livewire::test(EditPost::class, [
            'record' => $post->getRouteKey(),
        ])
            ->assertActionVisible('preview');
    }

    public function test_edit_page_hides_preview_action_for_published_posts(): void
    {
        $admin = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin);

        Livewire::test(EditPost::class, [
            'record' => $post->getRouteKey(),
        ])
            ->assertActionHidden('preview');
    }
}
