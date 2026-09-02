<?php

namespace Tests\Feature;

use App\Livewire\ArchiveList;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArchiveListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        Livewire::test(ArchiveList::class)
            ->assertSuccessful();
    }

    public function test_shows_only_published_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Published Post', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->draft()->create(['title' => 'Draft Post']);

        Livewire::test(ArchiveList::class)
            ->assertSee('Published Post')
            ->assertDontSee('Draft Post');
    }

    public function test_shows_years_in_dropdown(): void
    {
        Post::factory()->published()->create(['published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['published_at' => Carbon::parse('2023-03-10')]);

        Livewire::test(ArchiveList::class)
            ->assertSee('2024')
            ->assertSee('2023');
    }

    public function test_filter_by_year(): void
    {
        Post::factory()->published()->create(['title' => 'Post 2024', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['title' => 'Post 2023', 'published_at' => Carbon::parse('2023-03-10')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->assertSee('Post 2024')
            ->assertDontSee('Post 2023');
    }

    public function test_filter_by_month_within_year(): void
    {
        Post::factory()->published()->create(['title' => 'June Post', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['title' => 'March Post', 'published_at' => Carbon::parse('2024-03-10')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->set('month', '6')
            ->assertSee('June Post')
            ->assertDontSee('March Post');
    }

    public function test_changing_year_resets_month(): void
    {
        Post::factory()->published()->create(['title' => 'Post A', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['title' => 'Post B', 'published_at' => Carbon::parse('2023-03-10')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->set('month', '6')
            ->set('year', '2023')
            ->assertSet('month', '');
    }

    public function test_clearing_year_shows_all_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Post 2024', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['title' => 'Post 2023', 'published_at' => Carbon::parse('2023-03-10')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->set('year', '')
            ->assertSee('Post 2024')
            ->assertSee('Post 2023');
    }

    public function test_shows_empty_state_when_no_posts(): void
    {
        Livewire::test(ArchiveList::class)
            ->assertSee(__('No posts yet.'));
    }

    public function test_posts_grouped_by_year(): void
    {
        Post::factory()->published()->create(['title' => 'Post A', 'published_at' => Carbon::parse('2024-06-15')]);
        Post::factory()->published()->create(['title' => 'Post B', 'published_at' => Carbon::parse('2024-09-20')]);
        Post::factory()->published()->create(['title' => 'Post C', 'published_at' => Carbon::parse('2023-03-10')]);

        Livewire::test(ArchiveList::class)
            ->assertSee('2024')
            ->assertSee('2023')
            ->assertSee('Post A')
            ->assertSee('Post B')
            ->assertSee('Post C');
    }

    public function test_archive_uses_chronological_list_layout(): void
    {
        Post::factory()->published()->create([
            'title' => 'Archive List Post',
            'published_at' => Carbon::parse('2024-06-15'),
        ]);

        Livewire::test(ArchiveList::class)
            ->assertSee('<time datetime="2024-06-15"', false)
            ->assertSee('Archive List Post')
            ->assertDontSee('class="post-card-hover', false);
    }

    public function test_active_filter_badges_render_when_year_selected(): void
    {
        Post::factory()->published()->create(['published_at' => Carbon::parse('2024-06-15')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->assertSee(__('Year: :year', ['year' => '2024']), false)
            ->assertSee('wire:click="clearYear"', false);
    }

    public function test_clear_year_resets_month(): void
    {
        Post::factory()->published()->create(['published_at' => Carbon::parse('2024-06-15')]);

        Livewire::test(ArchiveList::class)
            ->set('year', '2024')
            ->set('month', '6')
            ->call('clearYear')
            ->assertSet('year', '')
            ->assertSet('month', '');
    }
}
