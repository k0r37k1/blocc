<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\PostList;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduledPostVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_post_is_not_visible_on_the_blog(): void
    {
        Post::factory()->scheduled()->create([
            'title' => 'Future Scheduled Post',
            'slug' => 'future-scheduled-post',
            'published_at' => now()->addDay(),
        ]);

        $this->get(route('blog.show', 'future-scheduled-post'))
            ->assertNotFound();

        Livewire::test(PostList::class)
            ->assertDontSee('Future Scheduled Post');
    }

    public function test_scheduled_post_becomes_visible_after_publish_command(): void
    {
        $post = Post::factory()->scheduled()->create([
            'title' => 'Soon Scheduled Post',
            'slug' => 'soon-scheduled-post',
            'published_at' => now()->addMinute(),
        ]);

        $this->travel(2)->minutes();

        $this->assertFalse($post->fresh()->isPubliclyVisible());

        $this->artisan('posts:publish-scheduled')->assertExitCode(0);

        $post->refresh();

        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->isPubliclyVisible());

        $this->get(route('blog.show', 'soon-scheduled-post'))
            ->assertOk()
            ->assertSee('Soon Scheduled Post');
    }

    public function test_future_publish_date_with_published_status_is_treated_as_scheduled(): void
    {
        $post = Post::factory()->draft()->create([
            'title' => 'Auto Scheduled Post',
            'slug' => 'auto-scheduled-post',
        ]);

        $post->update([
            'status' => PostStatus::Published,
            'published_at' => now()->addDay(),
        ]);

        $post->refresh();

        $this->assertSame(PostStatus::Scheduled, $post->status);
        $this->assertFalse($post->isPubliclyVisible());

        $this->get(route('blog.show', 'auto-scheduled-post'))
            ->assertNotFound();
    }
}
