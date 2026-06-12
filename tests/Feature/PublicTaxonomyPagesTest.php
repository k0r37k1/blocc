<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTaxonomyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_show_returns_ok_with_empty_tag(): void
    {
        $tag = Tag::factory()->create(['slug' => 'empty-tag']);

        $this->get(route('tag.show', $tag))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_tag_show_returns_ok_when_pagination_renders(): void
    {
        $tag = Tag::factory()->create(['slug' => 'vibe-coding']);
        $posts = Post::factory()->count(11)->published()->create();
        $tag->posts()->sync($posts->modelKeys());

        $this->get(route('tag.show', $tag))
            ->assertOk();
    }

    public function test_category_show_returns_ok_when_pagination_renders(): void
    {
        $category = Category::factory()->create();
        Post::factory()->count(11)->published()->create(['category_id' => $category->id]);

        $this->get(route('category.show', $category))
            ->assertOk();
    }

    public function test_empty_category_page_is_noindex(): void
    {
        $category = Category::factory()->create();

        $this->get(route('category.show', $category))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }
}
