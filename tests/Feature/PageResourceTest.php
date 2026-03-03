<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
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

        Livewire::test(ListPages::class)
            ->assertSuccessful();
    }

    public function test_list_page_shows_pages(): void
    {
        $this->actingAs($this->admin);

        $pages = Page::factory()->count(3)->create();

        Livewire::test(ListPages::class)
            ->assertCanSeeTableRecords($pages);
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePage::class)
            ->assertSuccessful();
    }

    public function test_can_create_page(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Impressum',
                'slug' => 'impressum',
                'body' => '<p>Legal information here.</p>',
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'title' => 'Impressum',
            'slug' => 'impressum',
            'status' => 'published',
        ]);
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePage::class)
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

        Page::factory()->create(['slug' => 'duplicate-slug']);

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Another Page',
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

        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertSuccessful();
    }

    public function test_can_edit_page(): void
    {
        $this->actingAs($this->admin);

        $page = Page::factory()->create([
            'title' => 'Original Title',
        ]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_page(): void
    {
        $this->actingAs($this->admin);

        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('pages', [
            'id' => $page->id,
        ]);
    }

    public function test_published_at_set_on_first_publish(): void
    {
        $page = Page::factory()->draft()->create();

        $this->assertNull($page->published_at);

        $page->update(['status' => PostStatus::Published]);

        $page->refresh();

        $this->assertNotNull($page->published_at);
    }

    public function test_form_has_required_fields(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePage::class)
            ->assertFormFieldExists('title')
            ->assertFormFieldExists('slug')
            ->assertFormFieldExists('body')
            ->assertFormFieldExists('status');
    }

    public function test_table_has_required_columns(): void
    {
        $this->actingAs($this->admin);

        Page::factory()->create();

        Livewire::test(ListPages::class)
            ->assertTableColumnExists('title')
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('is_published');
    }
}
