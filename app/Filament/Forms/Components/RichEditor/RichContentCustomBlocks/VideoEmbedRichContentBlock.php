<?php

namespace App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class VideoEmbedRichContentBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return 'video_embed';
    }

    public static function getLabel(): string
    {
        return __('Rich editor: Video embed');
    }

    public static function getPreviewLabel(array $config): string
    {
        $url = $config['url'] ?? '';

        return $url !== '' ? Str::limit($url, 72) : static::getLabel();
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription(__('Paste a YouTube or Vimeo page URL. The video is embedded responsively on the public site.'))
            ->schema([
                TextInput::make('url')
                    ->label(__('Video URL'))
                    ->url()
                    ->required()
                    ->placeholder('https://www.youtube.com/watch?v=…'),
            ]);
    }

    public static function toPreviewHtml(array $config): string
    {
        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.video-embed-rich-content.preview', [
            'url' => $config['url'] ?? '',
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    public static function toHtml(array $config, array $data): string
    {
        $embedUrl = self::resolveTrustedEmbedUrl($config['url'] ?? null);

        if ($embedUrl === null) {
            return '<p class="rich-embed-error">'.e(__('Invalid or unsupported video URL.')).'</p>';
        }

        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.video-embed-rich-content.index', [
            'embedUrl' => $embedUrl,
            'title' => __('Embedded video'),
        ])->render();
    }

    public static function resolveTrustedEmbedUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);

        if (str_ends_with($host, 'youtube.com')) {
            $path = (string) parse_url($url, PHP_URL_PATH);

            if (preg_match('#/embed/([a-zA-Z0-9_-]{11})#', $path, $matches) === 1) {
                return self::youtubeNocookieEmbedFromId($matches[1]);
            }

            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $id = $query['v'] ?? null;

            return self::youtubeNocookieEmbedFromId(is_string($id) ? $id : null);
        }

        if ($host === 'youtu.be') {
            $id = trim((string) parse_url($url, PHP_URL_PATH), '/');

            return self::youtubeNocookieEmbedFromId($id !== '' ? $id : null);
        }

        if (str_ends_with($host, 'vimeo.com') && ! str_starts_with($host, 'player.')) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            if (preg_match('#/(\d+)(?:/|$)#', $path, $matches) === 1) {
                return 'https://player.vimeo.com/video/'.$matches[1];
            }
        }

        return null;
    }

    private static function youtubeNocookieEmbedFromId(?string $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) !== 1) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }
}
