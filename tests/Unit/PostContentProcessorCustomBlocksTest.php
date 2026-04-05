<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Services\PostContentProcessor;
use Tests\TestCase;

class PostContentProcessorCustomBlocksTest extends TestCase
{
    private function processed(string $html, Post $post): string
    {
        $out = app(PostContentProcessor::class)->process($html, $post);
        $this->assertIsString($out);

        return $out;
    }

    public function test_expands_video_embed_custom_block_for_post_body(): void
    {
        $config = json_encode(['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'], JSON_UNESCAPED_SLASHES);
        $this->assertIsString($config);
        $encoded = htmlspecialchars($config, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = '<div data-type="customBlock" data-id="video_embed" data-config="'.$encoded.'"></div>';

        $post = Post::factory()->makeOne([
            'category_id' => null,
            'body' => $html,
        ]);

        $out = $this->processed($html, $post);

        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $out);
        $this->assertStringContainsString('<iframe', $out);
    }

    public function test_expands_callout_custom_block_for_post_body(): void
    {
        $config = json_encode([
            'variant' => 'warning',
            'title' => 'Watch out',
            'body' => "Line one\nLine two",
        ], JSON_UNESCAPED_UNICODE);
        $this->assertIsString($config);
        $encoded = htmlspecialchars($config, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = '<div data-type="customBlock" data-id="callout" data-config="'.$encoded.'"></div>';

        $post = Post::factory()->makeOne([
            'category_id' => null,
            'body' => $html,
        ]);

        $out = $this->processed($html, $post);

        $this->assertStringContainsString('callout--warning', $out);
        $this->assertStringContainsString('Watch out', $out);
        $this->assertStringContainsString('Line one', $out);
        $this->assertStringContainsString('<br', $out);
    }
}
