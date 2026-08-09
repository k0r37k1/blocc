<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

class PostCache
{
    public const TTL_SECONDS = 3600;

    public static function showKey(string $slug): string
    {
        return "posts:show:{$slug}";
    }

    public static function relatedKey(int|string $postId): string
    {
        return "posts:related:{$postId}";
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function rememberShow(string $slug, callable $callback): mixed
    {
        return Cache::remember(self::showKey($slug), self::TTL_SECONDS, $callback);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function rememberRelated(Post $post, callable $callback): mixed
    {
        return Cache::remember(self::relatedKey($post->getKey()), self::TTL_SECONDS, $callback);
    }

    public static function forgetFor(Post $post): void
    {
        Cache::forget(self::showKey($post->slug));

        if ($post->wasChanged('slug') && filled($post->getOriginal('slug'))) {
            Cache::forget(self::showKey((string) $post->getOriginal('slug')));
        }

        Cache::forget(self::relatedKey($post->getKey()));

        ResponseCache::clear();
    }

    public static function clearResponseCache(): void
    {
        ResponseCache::clear();
    }
}
