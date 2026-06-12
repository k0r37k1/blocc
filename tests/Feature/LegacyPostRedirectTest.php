<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyPostRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_post_url_redirects_to_blog_show(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'legacy-post-slug']);

        $this->get('/legacy-post-slug')
            ->assertRedirect(route('blog.show', $post))
            ->assertStatus(301);
    }

    public function test_legacy_post_url_for_draft_returns_not_found(): void
    {
        Post::factory()->draft()->create(['slug' => 'draft-only-slug']);

        $this->get('/draft-only-slug')
            ->assertNotFound();
    }

    public function test_unknown_legacy_slug_returns_not_found(): void
    {
        $this->get('/definitely-not-a-post')
            ->assertNotFound();
    }
}
