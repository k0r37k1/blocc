<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_returns_rss_xml_response(): void
    {
        $response = $this->get('/feed');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    public function test_feed_contains_published_posts(): void
    {
        $postA = Post::factory()->published()->create(['title' => 'Alpha Post Title']);
        $postB = Post::factory()->published()->create(['title' => 'Beta Post Title']);

        $response = $this->get('/feed');

        $response->assertSee('Alpha Post Title');
        $response->assertSee('Beta Post Title');
    }

    public function test_feed_excludes_draft_posts(): void
    {
        $draft = Post::factory()->draft()->create(['title' => 'Secret Draft Post']);

        $response = $this->get('/feed');

        $response->assertDontSee('Secret Draft Post');
    }

    public function test_feed_limits_to_twenty_posts(): void
    {
        Post::factory()->published()->count(25)->create();

        $response = $this->get('/feed');

        $this->assertEquals(20, substr_count($response->getContent(), '<item>'));
    }

    public function test_feed_contains_full_text_content(): void
    {
        $post = Post::factory()->published()->create([
            'body' => '<p>Unique paragraph content for testing CDATA.</p>',
        ]);

        $response = $this->get('/feed');

        $response->assertSee('<![CDATA[', false);
        $response->assertSee('Unique paragraph content for testing CDATA.', false);
    }

    public function test_feed_contains_post_tags(): void
    {
        $post = Post::factory()->published()->create();
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post->tags()->attach($tag);

        $response = $this->get('/feed');

        $response->assertSee('<category>Laravel</category>', false);
    }

    public function test_feed_posts_are_chronologically_descending(): void
    {
        $olderPost = Post::factory()->published()->create([
            'title' => 'Older Post',
            'published_at' => now()->subDays(5),
        ]);
        $newerPost = Post::factory()->published()->create([
            'title' => 'Newer Post',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/feed');

        $content = $response->getContent();
        $newerPosition = strpos($content, 'Newer Post');
        $olderPosition = strpos($content, 'Older Post');

        $this->assertLessThan($olderPosition, $newerPosition);
    }
}
