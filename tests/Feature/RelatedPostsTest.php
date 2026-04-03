<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_blog_show_includes_related_posts_when_tags_overlap(): void
    {
        $tag = Tag::factory()->create();

        $current = Post::factory()->published()->create(['title' => 'Current Post', 'slug' => 'current-post']);
        $current->tags()->attach($tag);

        $relatedA = Post::factory()->published()->create(['title' => 'Related Alpha', 'slug' => 'related-alpha']);
        $relatedA->tags()->attach($tag);

        $relatedB = Post::factory()->published()->create(['title' => 'Related Beta', 'slug' => 'related-beta']);
        $relatedB->tags()->attach($tag);

        $response = $this->get(route('blog.show', $current));

        $response->assertOk();
        $response->assertSee(__('Related posts (:count)', ['count' => 2]), false);
        $response->assertSee('Related Alpha');
        $response->assertSee('Related Beta');
    }

    public function test_blog_show_prefers_same_category_when_no_shared_tags(): void
    {
        $category = Category::factory()->create();

        $current = Post::factory()->published()->create([
            'title' => 'Lonely Post',
            'slug' => 'lonely-post',
            'category_id' => $category->id,
        ]);

        Post::factory()->published()->create([
            'title' => 'Same Category Post',
            'slug' => 'same-category-post',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('blog.show', $current));

        $response->assertOk();
        $response->assertSee(__('Related posts (:count)', ['count' => 1]), false);
        $response->assertSee('Same Category Post');
    }

    public function test_blog_show_hides_related_section_when_no_other_published_posts(): void
    {
        $only = Post::factory()->published()->create(['title' => 'Only Post', 'slug' => 'only-post']);

        $response = $this->get(route('blog.show', $only));

        $response->assertOk();
        $response->assertDontSee('Related posts (', false);
    }
}
