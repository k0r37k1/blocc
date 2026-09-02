<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Livewire\PostList;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogReadingExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
    }

    public function test_blog_show_includes_copy_link(): void
    {
        $post = Post::factory()->published()->create([
            'slug' => 'copy-link-post',
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('class="copy-link-btn"', false)
            ->assertSee('x-data="copyToClipboard"', false)
            ->assertSee(__('Copy link'), false)
            ->assertSee(__('Link copied!'), false)
            ->assertSee('class="copy-tooltip"', false);
    }

    public function test_blog_show_toc_toggle_is_accessible_button(): void
    {
        $category = Category::factory()->create();

        Post::factory()->create([
            'slug' => 'toc-post',
            'category_id' => $category->id,
            'status' => PostStatus::Published,
            'published_at' => now(),
            'toc_enabled' => true,
            'body' => '<h2>First section</h2><p>One.</p>'
                .'<h2>Second section</h2><p>Two.</p>'
                .'<h2>Third section</h2><p>Three.</p>',
        ]);

        $this->get(route('blog.show', 'toc-post'))
            ->assertOk()
            ->assertSee('class="toc-header"', false)
            ->assertSee('type="button"', false)
            ->assertSee('aria-controls="toc-list"', false)
            ->assertSee(':aria-expanded="open"', false);
    }

    public function test_blog_show_related_posts_section_is_open_by_default(): void
    {
        $category = Category::factory()->create();

        $current = Post::factory()->published()->create([
            'title' => 'Current',
            'slug' => 'current',
            'category_id' => $category->id,
        ]);

        Post::factory()->published()->create([
            'title' => 'Related sibling',
            'slug' => 'related-sibling',
            'category_id' => $category->id,
        ]);

        $this->get(route('blog.show', $current))
            ->assertOk()
            ->assertSee('<details class="related-posts', false)
            ->assertSee(' open>', false);
    }

    public function test_post_list_shows_loading_indicator_markup(): void
    {
        Livewire::test(PostList::class)
            ->assertSee(__('Loading…'), false)
            ->assertSee('wire:loading', false);
    }

    public function test_post_list_pagination_uses_livewire_buttons(): void
    {
        Post::factory()->count(12)->published()->create();

        Livewire::test(PostList::class)
            ->assertSee('wire:click="nextPage', false)
            ->assertSee('wire:click="gotoPage', false);
    }
}
