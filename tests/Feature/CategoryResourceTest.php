<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
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

        Livewire::test(ListCategories::class)
            ->assertSuccessful();
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateCategory::class)
            ->assertSuccessful();
    }

    public function test_can_create_category(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Laravel Tips',
                'slug' => 'laravel-tips',
                'description' => 'Tips and tricks for Laravel development.',
                'color' => '#16a34a',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Laravel Tips',
            'slug' => 'laravel-tips',
            'description' => 'Tips and tricks for Laravel development.',
            'color' => '#16a34a',
        ]);
    }

    public function test_edit_page_renders(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_update_category(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'slug' => 'updated-name',
                'description' => 'Updated description.',
                'color' => '#dc2626',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'description' => 'Updated description.',
            'color' => '#dc2626',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_slug_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        Category::factory()->create(['slug' => 'duplicate-slug']);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Another Category',
                'slug' => 'duplicate-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_name_is_required(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => '',
                'slug' => 'test-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_can_list_categories_with_post_count(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create();
        Post::factory()->count(3)->create(['category_id' => $category->id]);

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords([$category])
            ->assertTableColumnExists('posts_count');
    }
}
