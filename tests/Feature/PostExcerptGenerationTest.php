<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostExcerptGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_generated_excerpt_appends_ellipsis_within_one_sixty_chars(): void
    {
        $plain = str_repeat('w', 220);

        $post = Post::factory()->draft()->create([
            'title' => 'Long body excerpt',
            'slug' => 'long-body-excerpt-'.uniqid(),
            'excerpt' => null,
            'body_raw' => '<p>'.$plain.'</p>',
            'body' => '<p>'.$plain.'</p>',
        ]);

        $excerpt = $post->fresh()->excerpt;
        $this->assertNotNull($excerpt);
        $this->assertStringEndsWith('...', $excerpt);
        $this->assertLessThanOrEqual(160, mb_strlen($excerpt));
    }

    public function test_auto_generated_excerpt_omits_ellipsis_when_body_is_short(): void
    {
        $post = Post::factory()->draft()->create([
            'title' => 'Short body excerpt',
            'slug' => 'short-body-excerpt-'.uniqid(),
            'excerpt' => null,
            'body_raw' => '<p>Hello world</p>',
            'body' => '<p>Hello world</p>',
        ]);

        $this->assertSame('Hello world', $post->fresh()->excerpt);
    }
}
