<?php

namespace Tests\Feature;

use App\Livewire\PostList;
use App\Models\Post;
use App\Services\PostSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostFtsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_fts_table_is_available_on_sqlite(): void
    {
        $this->assertTrue(app(PostSearch::class)->ftsAvailable());
    }

    public function test_published_posts_are_indexed_and_searchable_via_fts(): void
    {
        Post::factory()->published()->create([
            'title' => 'Eloquent Performance Tips',
            'excerpt' => 'Indexing and queries',
            'body' => '<p>How to speed up Laravel queries</p>',
            'body_raw' => '<p>How to speed up Laravel queries</p>',
        ]);
        Post::factory()->published()->create([
            'title' => 'Vue Composition API',
            'excerpt' => 'Frontend only',
            'body' => '<p>Nothing about databases</p>',
            'body_raw' => '<p>Nothing about databases</p>',
        ]);

        Livewire::test(PostList::class)
            ->set('search', 'Eloquent')
            ->assertSee('Eloquent Performance Tips')
            ->assertDontSee('Vue Composition API');
    }

    public function test_fts_search_matches_body_content(): void
    {
        Post::factory()->published()->create([
            'title' => 'Quiet Title',
            'excerpt' => 'Short',
            'body' => '<p>UniqueZebraKeyword appears only here</p>',
            'body_raw' => '<p>UniqueZebraKeyword appears only here</p>',
        ]);
        Post::factory()->published()->create([
            'title' => 'Other Post',
            'excerpt' => 'Short',
            'body' => '<p>Unrelated content</p>',
            'body_raw' => '<p>Unrelated content</p>',
        ]);

        Livewire::test(PostList::class)
            ->set('search', 'UniqueZebraKeyword')
            ->assertSee('Quiet Title')
            ->assertDontSee('Other Post');
    }

    public function test_fts_search_handles_german_umlauts(): void
    {
        Post::factory()->published()->create(['title' => 'Über Laravel']);
        Post::factory()->published()->create(['title' => 'Vue.js Guide']);

        Livewire::test(PostList::class)
            ->set('search', 'uber')
            ->assertSee('Über Laravel')
            ->assertDontSee('Vue.js Guide');
    }

    public function test_deleting_a_post_removes_it_from_fts_index(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Disposable Search Hit']);

        Livewire::test(PostList::class)
            ->set('search', 'Disposable')
            ->assertSee('Disposable Search Hit');

        $post->delete();

        Livewire::test(PostList::class)
            ->set('search', 'Disposable')
            ->assertDontSee('Disposable Search Hit');
    }
}
