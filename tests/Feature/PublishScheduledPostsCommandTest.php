<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishScheduledPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_publishes_due_scheduled_posts(): void
    {
        $duePost = Post::factory()->scheduled()->create([
            'slug' => 'due-scheduled-post',
            'published_at' => now()->addHour(),
        ]);
        $futurePost = Post::factory()->scheduled()->create([
            'slug' => 'future-scheduled-post',
            'published_at' => now()->addDay(),
        ]);

        $this->travel(2)->hours();

        $this->artisan('posts:publish-scheduled')
            ->expectsOutputToContain('Published 1 scheduled post(s).')
            ->assertExitCode(0);

        $duePost->refresh();
        $futurePost->refresh();

        $this->assertSame(PostStatus::Published, $duePost->status);
        $this->assertSame(PostStatus::Scheduled, $futurePost->status);
    }

    public function test_command_reports_when_no_posts_are_due(): void
    {
        Post::factory()->scheduled()->create([
            'published_at' => now()->addDay(),
        ]);

        $this->artisan('posts:publish-scheduled')
            ->expectsOutputToContain('No scheduled posts to publish.')
            ->assertExitCode(0);
    }
}
