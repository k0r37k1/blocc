<?php

namespace Tests\Unit;

use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\VideoEmbedRichContentBlock;
use PHPUnit\Framework\TestCase;

class VideoEmbedRichContentBlockTest extends TestCase
{
    public function test_resolves_youtube_watch_url_to_nocookie_embed(): void
    {
        $url = VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $url);
    }

    public function test_resolves_youtu_be_url(): void
    {
        $url = VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('https://youtu.be/dQw4w9WgXcQ');

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $url);
    }

    public function test_resolves_youtube_embed_path(): void
    {
        $url = VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('https://www.youtube.com/embed/dQw4w9WgXcQ');

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $url);
    }

    public function test_resolves_vimeo_page_url(): void
    {
        $url = VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('https://vimeo.com/148751763');

        $this->assertSame('https://player.vimeo.com/video/148751763', $url);
    }

    public function test_rejects_non_video_urls(): void
    {
        $this->assertNull(VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('https://example.com/video'));
        $this->assertNull(VideoEmbedRichContentBlock::resolveTrustedEmbedUrl('not-a-url'));
        $this->assertNull(VideoEmbedRichContentBlock::resolveTrustedEmbedUrl(''));
        $this->assertNull(VideoEmbedRichContentBlock::resolveTrustedEmbedUrl(null));
    }
}
