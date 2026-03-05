<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml_response(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function test_sitemap_contains_published_posts(): void
    {
        $postA = Post::factory()->published()->create();
        $postB = Post::factory()->published()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('blog.show', $postA));
        $response->assertSee(route('blog.show', $postB));
    }

    public function test_sitemap_excludes_draft_posts(): void
    {
        $draft = Post::factory()->draft()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('blog.show', $draft));
    }

    public function test_sitemap_contains_published_pages(): void
    {
        $page = Page::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('page.show', $page));
    }

    public function test_sitemap_excludes_draft_pages(): void
    {
        $page = Page::factory()->draft()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('page.show', $page));
    }

    public function test_sitemap_contains_static_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee(url('/'));
        $response->assertSee(route('archive'));
    }

    public function test_sitemap_contains_categories_and_tags(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('category.show', $category));
        $response->assertSee(route('tag.show', $tag));
    }

    public function test_sitemap_contains_lastmod_for_posts(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<lastmod>'.$post->updated_at->toW3cString().'</lastmod>', false);
    }
}
