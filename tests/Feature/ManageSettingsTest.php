<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_settings_page_renders_without_reset_database_action(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ManageSettings::class)
            ->assertSuccessful()
            ->assertActionDoesNotExist('resetData');
    }

    public function test_settings_page_uses_tabs_for_sections(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ManageSettings::class)
            ->assertSuccessful()
            ->assertSee(__('General'), false)
            ->assertSee(__('Appearance'), false)
            ->assertSee(__('Newsletter'), false)
            ->assertSee('fi-tabs', false)
            ->assertSee(__('Enable Comments'), false)
            ->assertFormFieldExists('blog_name')
            ->assertFormFieldExists('accent_color')
            ->assertFormFieldExists('newsletter_enabled')
            ->assertFormFieldExists('footer_text');
    }

    public function test_settings_can_be_saved_across_tabs(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ManageSettings::class)
            ->set('data.blog_name', 'Tabbed Blog')
            ->set('data.accent_color', '#112233')
            ->set('data.comments_enabled', false)
            ->set('data.footer_text', 'Footer from tabs')
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified(__('Settings saved'));

        $this->assertSame('Tabbed Blog', Setting::get('blog_name'));
        $this->assertSame('#112233', Setting::get('accent_color'));
        $this->assertSame('0', Setting::get('comments_enabled'));
        $this->assertSame('Footer from tabs', Setting::get('footer_text'));
    }
}
