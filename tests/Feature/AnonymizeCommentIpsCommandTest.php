<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AnonymizeCommentIpsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_clears_ip_for_comments_older_than_retention(): void
    {
        config(['comments.ip_retention_days' => 30]);

        $post = Post::factory()->published()->create();

        $old = Comment::factory()->create([
            'post_id' => $post->id,
            'ip_address' => '203.0.113.10',
            'created_at' => Carbon::parse('2020-01-01 12:00:00'),
        ]);

        $recent = Comment::factory()->create([
            'post_id' => $post->id,
            'ip_address' => '198.51.100.5',
            'created_at' => now()->subDays(5),
        ]);

        Artisan::call('comments:anonymize-ips');

        $this->assertNull($old->fresh()->ip_address);
        $this->assertSame('198.51.100.5', $recent->fresh()->ip_address);
    }

    public function test_dry_run_does_not_update_rows(): void
    {
        config(['comments.ip_retention_days' => 1]);

        $post = Post::factory()->published()->create();

        $old = Comment::factory()->create([
            'post_id' => $post->id,
            'ip_address' => '203.0.113.10',
            'created_at' => now()->subDays(10),
        ]);

        Artisan::call('comments:anonymize-ips', ['--dry-run' => true]);

        $this->assertSame('203.0.113.10', $old->fresh()->ip_address);
    }
}
