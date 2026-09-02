<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
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
}
