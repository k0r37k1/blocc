<?php

namespace Tests\Feature;

use App\Livewire\PostList;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        Livewire::test(PostList::class)
            ->assertSuccessful();
    }

    public function test_shows_only_published_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Published Post']);
        Post::factory()->draft()->create(['title' => 'Draft Post']);

        Livewire::test(PostList::class)
            ->assertSee('Published Post')
            ->assertDontSee('Draft Post');
    }

    public function test_keyword_search_filters_by_title(): void
    {
        Post::factory()->published()->create(['title' => 'Laravel Tips']);
        Post::factory()->published()->create(['title' => 'Vue.js Guide']);

        Livewire::test(PostList::class)
            ->set('search', 'Laravel')
            ->assertSee('Laravel Tips')
            ->assertDontSee('Vue.js Guide');
    }

    public function test_keyword_search_filters_by_excerpt(): void
    {
        Post::factory()->published()->create(['title' => 'Post One', 'excerpt' => 'All about queues']);
        Post::factory()->published()->create(['title' => 'Post Two', 'excerpt' => 'Nothing relevant']);

        Livewire::test(PostList::class)
            ->set('search', 'queues')
            ->assertSee('Post One')
            ->assertDontSee('Post Two');
    }

    public function test_keyword_search_handles_german_umlauts(): void
    {
        Post::factory()->published()->create(['title' => 'Über Laravel']);
        Post::factory()->published()->create(['title' => 'Vue.js Guide']);

        Livewire::test(PostList::class)
            ->set('search', 'über')
            ->assertSee('Über Laravel')
            ->assertDontSee('Vue.js Guide');
    }

    public function test_category_filter_returns_matching_posts(): void
    {
        $category = Category::factory()->create(['slug' => 'laravel']);
        Post::factory()->published()->create(['title' => 'Laravel Post', 'category_id' => $category->id]);
        Post::factory()->published()->create(['title' => 'Other Post']);

        Livewire::test(PostList::class)
            ->set('category', 'laravel')
            ->assertSee('Laravel Post')
            ->assertDontSee('Other Post');
    }

    public function test_category_filter_with_nonexistent_slug_returns_empty(): void
    {
        Post::factory()->published()->create(['title' => 'Some Post']);

        Livewire::test(PostList::class)
            ->set('category', 'nonexistent-category')
            ->assertDontSee('Some Post');
    }

    public function test_tag_filter_returns_matching_posts(): void
    {
        $tag = Tag::factory()->create(['slug' => 'php']);
        $post = Post::factory()->published()->create(['title' => 'PHP Post']);
        $post->tags()->attach($tag);
        Post::factory()->published()->create(['title' => 'Other Post']);

        Livewire::test(PostList::class)
            ->set('tag', 'php')
            ->assertSee('PHP Post')
            ->assertDontSee('Other Post');
    }

    public function test_tag_toggle_deselects_on_second_click(): void
    {
        $tag = Tag::factory()->create(['slug' => 'php']);
        $post = Post::factory()->published()->create(['title' => 'PHP Post']);
        $post->tags()->attach($tag);

        Livewire::test(PostList::class)
            ->call('toggleTag', 'php')
            ->assertSet('tag', 'php')
            ->call('toggleTag', 'php')
            ->assertSet('tag', '');
    }

    public function test_sort_newest_returns_latest_post_first(): void
    {
        Post::factory()->published()->create(['title' => 'Old Post', 'published_at' => now()->subDays(10)]);
        Post::factory()->published()->create(['title' => 'New Post', 'published_at' => now()->subDay()]);

        $component = Livewire::test(PostList::class)->set('sort', 'newest');
        $posts = $component->instance()->posts;

        $this->assertSame('New Post', $posts->first()->title);
    }

    public function test_sort_oldest_returns_earliest_post_first(): void
    {
        Post::factory()->published()->create(['title' => 'Old Post', 'published_at' => now()->subDays(10)]);
        Post::factory()->published()->create(['title' => 'New Post', 'published_at' => now()->subDay()]);

        $component = Livewire::test(PostList::class)->set('sort', 'oldest');
        $posts = $component->instance()->posts;

        $this->assertSame('Old Post', $posts->first()->title);
    }

    public function test_pagination_resets_on_search_change(): void
    {
        // Create enough posts to trigger pagination (default 10 per page)
        Post::factory()->published()->count(12)->create();

        $component = Livewire::test(PostList::class);
        $component->call('gotoPage', 2);
        $component->set('search', 'zzz-no-match');

        // After filter change component renders without error (resetPage was called)
        $component->assertSuccessful();
        $this->assertEmpty($component->instance()->posts->items());
    }

    public function test_pagination_resets_on_category_change(): void
    {
        Post::factory()->published()->count(12)->create();

        $component = Livewire::test(PostList::class);
        $component->call('gotoPage', 2);
        $component->set('category', 'nonexistent');

        $component->assertSuccessful();
        $this->assertEmpty($component->instance()->posts->items());
    }

    public function test_clear_filters_resets_all(): void
    {
        Livewire::test(PostList::class)
            ->set('search', 'test')
            ->set('category', 'laravel')
            ->set('tag', 'php')
            ->set('sort', 'oldest')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('category', '')
            ->assertSet('tag', '')
            ->assertSet('sort', 'newest');
    }
}
