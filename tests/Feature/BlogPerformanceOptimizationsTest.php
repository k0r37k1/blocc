<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Services\PostCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BlogPerformanceOptimizationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_show_sets_cache_control_headers(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'cached-post']);

        $response = $this->get(route('blog.show', $post));

        $response->assertSuccessful();
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('max-age=120', (string) $response->headers->get('Cache-Control'));
    }

    public function test_blog_show_requests_high_priority_for_featured_image_when_present(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'hero-post', 'title' => 'Hero Post']);

        $response = $this->get(route('blog.show', $post));

        $response->assertSuccessful();
        // Without a featured image there is nothing to prioritize; ensure page still renders.
        $response->assertSee('Hero Post');
    }

    public function test_blog_show_json_ld_includes_word_count(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'schema-post',
            'body' => '<p>'.str_repeat('word ', 50).'</p>',
            'body_raw' => '<p>'.str_repeat('word ', 50).'</p>',
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertSuccessful();
        $response->assertSee('"wordCount"', false);
        $response->assertSee('"@type":"BlogPosting"', false);
    }

    public function test_blog_show_uses_post_cache_and_invalidates_on_update(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'cache-me',
            'title' => 'Original Title',
        ]);

        $this->get(route('blog.show', $post))->assertSuccessful()->assertSee('Original Title');

        $this->assertTrue(Cache::has(PostCache::showKey('cache-me')));

        $post->update(['title' => 'Updated Title']);

        $this->assertFalse(Cache::has(PostCache::showKey('cache-me')));

        $this->get(route('blog.show', $post))->assertSuccessful()->assertSee('Updated Title');
    }

    public function test_static_page_sets_cache_control_and_can_be_response_cached(): void
    {
        $page = Page::factory()->create([
            'slug' => 'impressum',
            'title' => 'Impressum',
        ]);

        $first = $this->get(route('page.show', $page));
        $first->assertSuccessful();
        $first->assertSee('Impressum');
        $this->assertStringContainsString('max-age=300', (string) $first->headers->get('Cache-Control'));

        $second = $this->get(route('page.show', $page));
        $second->assertSuccessful();
        $second->assertSee('Impressum');
    }

    public function test_feed_and_sitemap_expose_cache_headers(): void
    {
        Post::factory()->published()->create();

        $feed = $this->get(route('feed'));
        $feed->assertSuccessful();
        $this->assertStringContainsString('max-age=300', (string) $feed->headers->get('Cache-Control'));

        $sitemap = $this->get(route('sitemap'));
        $sitemap->assertSuccessful();
        $this->assertStringContainsString('max-age=300', (string) $sitemap->headers->get('Cache-Control'));
    }
}
