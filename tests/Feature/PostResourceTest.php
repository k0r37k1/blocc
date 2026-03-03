<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostResourceTest extends TestCase
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

        Livewire::test(ListPosts::class)
            ->assertSuccessful();
    }

    public function test_list_page_shows_posts(): void
    {
        $this->actingAs($this->admin);

        $posts = Post::factory()->count(3)->create();

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords($posts);
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePost::class)
            ->assertSuccessful();
    }

    public function test_can_create_post(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'My Test Post',
                'slug' => 'my-test-post',
                'body' => '<p>This is test content.</p>',
                'status' => 'draft',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'title' => 'My Test Post',
            'slug' => 'my-test-post',
            'status' => 'draft',
            'category_id' => $category->id,
        ]);
    }

    public function test_can_create_published_post(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Published Post',
                'slug' => 'published-post',
                'body' => '<p>Published content.</p>',
                'status' => 'published',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::query()->where('slug', 'published-post')->first();

        $this->assertNotNull($post);
        $this->assertEquals(PostStatus::Published, $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => '',
                'slug' => 'test-slug',
                'body' => '<p>Content.</p>',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    }

    public function test_slug_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->create(['slug' => 'duplicate-slug']);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Another Post',
                'slug' => 'duplicate-slug',
                'body' => '<p>Content.</p>',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_edit_page_renders(): void
    {
        $this->actingAs($this->admin);

        $post = Post::factory()->create();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_edit_post(): void
    {
        $this->actingAs($this->admin);

        $post = Post::factory()->draft()->create([
            'title' => 'Original Title',
        ]);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_post(): void
    {
        $this->actingAs($this->admin);

        $post = Post::factory()->create();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_published_at_set_on_first_publish(): void
    {
        $this->actingAs($this->admin);

        $post = Post::factory()->draft()->create();

        $this->assertNull($post->published_at);

        $post->update(['status' => PostStatus::Published]);

        $post->refresh();

        $this->assertNotNull($post->published_at);
    }

    public function test_published_at_preserved_on_republish(): void
    {
        $this->actingAs($this->admin);

        $post = Post::factory()->published()->create();

        $originalPublishedAt = $post->published_at;

        $post->update(['status' => PostStatus::Draft]);
        $post->refresh();

        $post->update(['status' => PostStatus::Published]);
        $post->refresh();

        $this->assertEquals(
            $originalPublishedAt->timestamp,
            $post->published_at->timestamp,
        );
    }

    public function test_form_has_required_fields(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('title')
            ->assertFormFieldExists('slug')
            ->assertFormFieldExists('category_id')
            ->assertFormFieldExists('tags')
            ->assertFormFieldExists('body')
            ->assertFormFieldExists('excerpt')
            ->assertFormFieldExists('featured_image')
            ->assertFormFieldExists('featured_image_alt')
            ->assertFormFieldExists('status');
    }

    public function test_table_has_required_columns(): void
    {
        $this->actingAs($this->admin);

        Post::factory()->create();

        Livewire::test(ListPosts::class)
            ->assertTableColumnExists('title')
            ->assertTableColumnExists('category.name')
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('is_published');
    }
}
