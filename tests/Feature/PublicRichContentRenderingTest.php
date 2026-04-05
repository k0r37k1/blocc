<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ensures Rich Editor HTML survives {@see \App\Services\PostContentProcessor} on save and is present
 * in public post/page views (not just in admin or unit-isolated processing).
 */
class PublicRichContentRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_public_view_outputs_processed_formatted_body(): void
    {
        $category = Category::factory()->create();

        $rawBody = '<p><strong>BloccPostStrong</strong> and <em>BloccPostEm</em>.</p>'
            .'<ul><li>BloccPostListItem</li></ul>'
            .'<h2>Blocc Post Heading</h2>';

        Post::factory()->create([
            'title' => 'Rich Post',
            'slug' => 'rich-post',
            'category_id' => $category->id,
            'status' => PostStatus::Published,
            'published_at' => now(),
            'body' => $rawBody,
        ]);

        $post = Post::query()->where('slug', 'rich-post')->firstOrFail();
        $this->assertSame($rawBody, $post->body_raw, 'editor HTML should be stored in body_raw');
        $this->assertStringContainsString('BloccPostStrong', $post->body);
        $this->assertStringContainsString('<strong>', $post->body);
        $this->assertStringContainsString('Blocc Post Heading', $post->body);
        $this->assertStringContainsString('heading-anchor', $post->body, 'h2 should get TOC anchor markup');

        $response = $this->get(route('blog.show', 'rich-post'));
        $response->assertOk();
        $response->assertSee('BloccPostStrong', false);
        $response->assertSee('BloccPostEm', false);
        $response->assertSee('BloccPostListItem', false);
        $response->assertSee('Blocc Post Heading', false);
        $response->assertSee('<ul>', false);
        $response->assertSee('<li>', false);
    }

    public function test_published_page_public_view_outputs_processed_formatted_body(): void
    {
        $rawBody = '<p><strong>BloccPageStrong</strong></p><blockquote><p>BloccQuote</p></blockquote>';

        Page::factory()->create([
            'title' => 'Rich Page',
            'slug' => 'rich-page',
            'status' => PostStatus::Published,
            'published_at' => now(),
            'body' => $rawBody,
        ]);

        $page = Page::query()->where('slug', 'rich-page')->firstOrFail();
        $this->assertSame($rawBody, $page->body_raw, 'editor HTML should be stored in body_raw');
        $this->assertStringContainsString('BloccPageStrong', $page->body);
        $this->assertStringContainsString('<strong>', $page->body);

        $response = $this->get(route('page.show', 'rich-page'));
        $response->assertOk();
        $response->assertSee('BloccPageStrong', false);
        $response->assertSee('BloccQuote', false);
        $response->assertSee('<blockquote>', false);
    }
}
