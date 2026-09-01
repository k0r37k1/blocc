<?php

namespace App\Observers;

use App\Jobs\SubmitIndexNowUrls;
use App\Models\Post;
use App\Services\PostCache;
use App\Services\PostSearch;

class PostObserver
{
    public function __construct(private PostSearch $postSearch) {}

    /**
     * New posts inserted already as published never populate `wasChanged('status')` on `saved`
     * (Laravel does not sync insert dirty attributes into `changes`), so we handle that here.
     */
    public function created(Post $post): void
    {
        $this->syncSearchIndex($post);
        PostCache::forgetFor($post);

        if (! $post->isPubliclyVisible()) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    public function updated(Post $post): void
    {
        $this->syncSearchIndex($post);
        PostCache::forgetFor($post);

        if (! $post->isPubliclyVisible()) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    public function deleted(Post $post): void
    {
        $this->postSearch->remove($post);
        PostCache::forgetFor($post);
    }

    private function syncSearchIndex(Post $post): void
    {
        if ($post->isPubliclyVisible()) {
            $this->postSearch->sync($post);
        } else {
            $this->postSearch->remove($post);
        }
    }

    private function submitToIndexNow(Post $post): void
    {
        if (! filled(config('indexnow.key'))) {
            return;
        }

        SubmitIndexNowUrls::dispatch([
            route('blog.show', $post, absolute: true),
        ]);
    }
}
