<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostAutosaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_autosaves_without_redirect(): void
    {
        $admin = User::factory()->create();
        $post = Post::factory()->draft()->create([
            'title' => 'Before autosave',
        ]);

        $this->actingAs($admin);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm([
                'title' => 'After autosave',
            ])
            ->call('autosave')
            ->assertHasNoFormErrors()
            ->assertSet('lastAutosavedAt', fn (?string $value): bool => filled($value));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'After autosave',
        ]);
    }

    public function test_autosave_skips_when_there_are_no_changes(): void
    {
        $admin = User::factory()->create();
        $post = Post::factory()->draft()->create([
            'title' => 'Unchanged title',
        ]);

        $originalUpdatedAt = $post->updated_at;

        $this->actingAs($admin);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->call('autosave')
            ->assertHasNoFormErrors();

        $post->refresh();

        $this->assertTrue($originalUpdatedAt->equalTo($post->updated_at));
    }

    public function test_edit_page_shows_last_saved_label(): void
    {
        $admin = User::factory()->create();
        $post = Post::factory()->draft()->create();

        $this->actingAs($admin);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertSee(__('Last saved at :time', [
                'time' => $post->updated_at->locale(app()->getLocale())->isoFormat('LLL'),
            ]));
    }
}
