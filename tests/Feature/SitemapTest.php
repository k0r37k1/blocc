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

    public function test_sitemap_excludes_legal_and_accessibility_pages(): void
    {
        $imprint = Page::factory()->create(['slug' => 'impressum']);
        $privacy = Page::factory()->create(['slug' => 'datenschutz']);
        $accessibility = Page::factory()->create(['slug' => 'barrierefreiheit']);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('page.show', $imprint));
        $response->assertDontSee(route('page.show', $privacy));
        $response->assertDontSee(route('page.show', $accessibility));
    }

    public function test_sitemap_excludes_draft_pages(): void
    {
        $page = Page::factory()->draft()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('page.show', $page));
    }

    public function test_sitemap_excludes_system_pages(): void
    {
        $systemPage = Page::factory()->create([
            'slug' => '__sys_test_blog',
            'is_system' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('page.show', $systemPage));
    }

    public function test_sitemap_contains_static_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee(url('/'));
        $response->assertSee(route('archive'));
    }

    public function test_sitemap_contains_categories_and_tags_with_published_posts(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);
        $post->tags()->attach($tag);

        $response = $this->get('/sitemap.xml');

        $response->assertSee(route('category.show', $category));
        $response->assertSee(route('tag.show', $tag));
    }

    public function test_sitemap_excludes_empty_categories_and_tags(): void
    {
        $emptyCategory = Category::factory()->create(['slug' => 'empty-category']);
        $emptyTag = Tag::factory()->create(['slug' => 'empty-tag']);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee(route('category.show', $emptyCategory));
        $response->assertDontSee(route('tag.show', $emptyTag));
    }

    public function test_sitemap_contains_lastmod_for_posts(): void
    {
        $post = Post::factory()->published()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('<lastmod>'.$post->updated_at->toW3cString().'</lastmod>', false);
    }
}
