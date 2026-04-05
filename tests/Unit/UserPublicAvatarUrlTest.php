<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPublicAvatarUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_avatar_url_falls_back_to_gravatar_without_upload(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);

        $url = $user->publicAvatarUrl(80);

        $this->assertStringContainsString('https://www.gravatar.com/avatar/', $url);
        $this->assertStringContainsString(md5('alice@example.com'), $url);
    }
}
