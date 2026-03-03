<?php

namespace Tests\Feature;

use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_list_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListTags::class)
            ->assertSuccessful();
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateTag::class)
            ->assertSuccessful();
    }

    public function test_can_create_tag(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateTag::class)
            ->fillForm([
                'name' => 'PHP',
                'slug' => 'php',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tags', [
            'name' => 'PHP',
            'slug' => 'php',
        ]);
    }

    public function test_edit_page_renders(): void
    {
        $this->actingAs($this->admin);

        $tag = Tag::factory()->create();

        Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_update_tag(): void
    {
        $this->actingAs($this->admin);

        $tag = Tag::factory()->create([
            'name' => 'Original Tag',
            'slug' => 'original-tag',
        ]);

        Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Tag',
                'slug' => 'updated-tag',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
        ]);
    }

    public function test_can_delete_tag(): void
    {
        $this->actingAs($this->admin);

        $tag = Tag::factory()->create();

        Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_slug_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Tag::factory()->create(['slug' => 'duplicate-slug']);

        Livewire::test(CreateTag::class)
            ->fillForm([
                'name' => 'Another Tag',
                'slug' => 'duplicate-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_name_is_required(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateTag::class)
            ->fillForm([
                'name' => '',
                'slug' => 'test-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_can_list_tags_with_post_count(): void
    {
        $this->actingAs($this->admin);

        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create();
        $tag->posts()->attach($posts->pluck('id'));

        Livewire::test(ListTags::class)
            ->assertCanSeeTableRecords([$tag])
            ->assertTableColumnExists('posts_count');
    }
}
