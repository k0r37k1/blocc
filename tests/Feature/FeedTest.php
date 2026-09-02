<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        Post::factory()->published()->create(['title' => 'Alpha Post Title']);
        Post::factory()->published()->create(['title' => 'Beta Post Title']);

        $response = $this->get('/feed');

        $response->assertSee('Alpha Post Title');
        $response->assertSee('Beta Post Title');
    }

    public function test_feed_excludes_draft_posts(): void
    {
        Post::factory()->draft()->create(['title' => 'Secret Draft Post']);

        $response = $this->get('/feed');

        $response->assertDontSee('Secret Draft Post');
    }

    public function test_feed_limits_to_twenty_posts(): void
    {
        Post::factory()->published()->count(25)->create();

        $response = $this->get('/feed');

        $this->assertEquals(20, substr_count($response->getContent(), '<item>'));
    }

    public function test_feed_contains_full_text_in_content_encoded(): void
    {
        $post = Post::factory()->published()->create([
            'body' => '<p>Unique paragraph content for testing CDATA.</p>',
        ]);

        $response = $this->get('/feed');

        $response->assertSee('<content:encoded>', false);
        $response->assertSee('Unique paragraph content for testing CDATA.', false);
    }

    public function test_feed_uses_blog_settings_for_channel_metadata(): void
    {
        Setting::setMany([
            'blog_name' => 'My Custom Feed',
            'blog_description' => 'Custom feed description',
        ]);

        Post::factory()->published()->create();

        $response = $this->get('/feed');

        $response->assertSee('<title>My Custom Feed</title>', false);
        $response->assertSee('<description>Custom feed description</description>', false);
    }

    public function test_feed_includes_author_when_present(): void
    {
        $author = User::factory()->create([
            'email' => 'author@example.com',
            'name' => 'Jane Author',
        ]);

        Post::factory()->published()->create([
            'user_id' => $author->id,
            'title' => 'Authored Post',
        ]);

        $response = $this->get('/feed');

        $response->assertSee('<author>author@example.com (Jane Author)</author>', false);
    }

    public function test_feed_includes_featured_image_enclosure(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Image Post']);
        $post->addMedia(UploadedFile::fake()->image('feed.jpg'))
            ->toMediaCollection('featured-image');

        $response = $this->get('/feed');

        $response->assertSee('<enclosure url=', false);
        $response->assertSee('type="image/', false);
    }

    public function test_feed_absolutizes_relative_image_urls(): void
    {
        Post::factory()->published()->create([
            'body' => '<p><img src="/storage/test.jpg" alt="Test"></p>',
        ]);

        $this->get('/feed')
            ->assertSee('src="'.url('/storage/test.jpg').'"', false);
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
        Post::factory()->published()->create([
            'title' => 'Older Post',
            'published_at' => now()->subDays(5),
        ]);
        Post::factory()->published()->create([
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
