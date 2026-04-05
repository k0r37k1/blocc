<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommentDisplayAvatarUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_comment_uses_gravatar(): void
    {
        $post = Post::factory()->published()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'email' => 'guest@example.com',
            'is_author' => false,
        ]);

        $url = $comment->displayAvatarUrl(80);

        $this->assertStringContainsString('https://www.gravatar.com/avatar/', $url);
        $this->assertStringContainsString(md5('guest@example.com'), $url);
    }

    public function test_author_comment_matches_user_public_avatar_url(): void
    {
        $user = User::factory()->create(['email' => 'author@example.com']);
        $post = Post::factory()->published()->create();
        $comment = Comment::factory()->asAuthor()->create([
            'post_id' => $post->id,
            'email' => 'author@example.com',
        ]);

        $this->assertSame($user->publicAvatarUrl(96), $comment->displayAvatarUrl(96));
    }

    public function test_author_comment_without_matching_user_falls_back_to_gravatar(): void
    {
        $post = Post::factory()->published()->create();
        $comment = Comment::factory()->asAuthor()->create([
            'post_id' => $post->id,
            'email' => 'nobody@example.com',
        ]);

        $url = $comment->displayAvatarUrl(80);

        $this->assertStringContainsString(md5('nobody@example.com'), $url);
    }

    public function test_author_comment_uses_uploaded_avatar_when_user_has_media(): void
    {
        $user = User::factory()->create(['email' => 'uploader@example.com']);
        $user->addMedia(UploadedFile::fake()->image('face.jpg', 80, 80))
            ->toMediaCollection('avatar');

        $post = Post::factory()->published()->create();
        $comment = Comment::factory()->asAuthor()->create([
            'post_id' => $post->id,
            'email' => 'uploader@example.com',
        ]);

        $url = $comment->displayAvatarUrl(80);

        $this->assertStringNotContainsString('gravatar.com', $url);
        $this->assertSame($user->publicAvatarUrl(80), $url);
    }
}
