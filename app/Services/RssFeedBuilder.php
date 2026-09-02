<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RssFeedBuilder
{
    public function channelTitle(): string
    {
        return (string) Setting::get('blog_name', config('app.name'));
    }

    public function channelDescription(): string
    {
        return (string) Setting::get('blog_description', config('app.description', ''));
    }

    /**
     * @param  Collection<int, Post>  $posts
     */
    public function lastBuildDate(Collection $posts): string
    {
        return ($posts->first()?->published_at ?? now())->format('r');
    }

    public function entryDescription(Post $post): string
    {
        return (string) ($post->excerpt ?: strip_tags($post->body ?? ''));
    }

    public function entryContent(Post $post): string
    {
        $html = $this->absolutizeUrls((string) ($post->body ?? ''));

        $enclosure = $this->featuredImageEnclosure($post);

        if ($enclosure === null) {
            return $html;
        }

        $alt = e($post->featured_image_alt ?? $post->title);
        $hero = sprintf(
            '<p><img src="%s" alt="%s" /></p>',
            e($enclosure['url']),
            $alt,
        );

        return $hero.$html;
    }

    /**
     * @return array{url: string, length: int, type: string}|null
     */
    public function featuredImageEnclosure(Post $post): ?array
    {
        $media = $post->getFirstMedia('featured-image');

        if (! $media instanceof Media) {
            return null;
        }

        $url = $media->hasGeneratedConversion('medium')
            ? $media->getFullUrl('medium')
            : $media->getFullUrl();

        return [
            'url' => $url,
            'length' => (int) $media->size,
            'type' => (string) $media->mime_type,
        ];
    }

    public function entryAuthor(Post $post): ?string
    {
        $author = $post->author;

        if ($author === null || blank($author->email)) {
            return null;
        }

        $name = filled($author->name) ? $author->name : $author->email;

        return sprintf('%s (%s)', $author->email, $name);
    }

    private function absolutizeUrls(string $html): string
    {
        $normalized = preg_replace_callback(
            '/\s(href|src)=(["\'])(\/[^"\']*)\2/i',
            fn (array $matches): string => ' '.$matches[1].'='.$matches[2].url($matches[3]).$matches[2],
            $html,
        );

        return is_string($normalized) ? $normalized : $html;
    }
}
