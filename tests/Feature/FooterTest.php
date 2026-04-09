<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_icon_only_admin_login_link_in_footer(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('filament.admin.auth.login'), false);
        $response->assertSee('aria-label="'.__('Login').'"', false);
    }

    public function test_authenticated_user_does_not_see_footer_admin_login_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertDontSee(route('filament.admin.auth.login'), false);
    }
}
