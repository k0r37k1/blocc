<?php

namespace Tests\Feature;

use App\Filament\Widgets\BlogStatsOverview;
use App\Filament\Widgets\DraftReminderWidget;
use App\Filament\Widgets\RecentPostsWidget;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_blog_stats_overview_shows_correct_counts(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->published()->count(3)->create();
        Post::factory()->draft()->count(2)->create();
        Page::factory()->count(4)->create();

        Livewire::test(BlogStatsOverview::class)
            ->assertSuccessful()
            ->assertSee(__('Published Posts'))
            ->assertSee('3')
            ->assertSee(__('Drafts'))
            ->assertSee('2')
            ->assertSee(__('Pages'))
            ->assertSee('4');
    }

    public function test_recent_posts_widget_shows_latest_posts(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->count(4)->create();
        $latestPost = Post::factory()->create([
            'title' => 'Latest Test Post',
            'updated_at' => now(),
        ]);

        Livewire::test(RecentPostsWidget::class)
            ->assertSuccessful()
            ->assertSee($latestPost->title);
    }

    public function test_draft_reminder_shows_draft_count(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->draft()->count(3)->create();

        Livewire::test(DraftReminderWidget::class)
            ->assertSuccessful()
            ->assertSee(trans_choice('You have :count draft|You have :count drafts', 3));
    }

    public function test_draft_reminder_shows_no_drafts_message(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->published()->count(2)->create();

        Livewire::test(DraftReminderWidget::class)
            ->assertSuccessful()
            ->assertSee(__('No drafts. All caught up!'));
    }
}
