<?php

namespace App\Observers;

use App\Enums\PostStatus;
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
        $this->postSearch->sync($post);
        PostCache::forgetFor($post);

        if ($post->status !== PostStatus::Published) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    public function updated(Post $post): void
    {
        $this->postSearch->sync($post);
        PostCache::forgetFor($post);

        if ($post->status !== PostStatus::Published) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    public function deleted(Post $post): void
    {
        $this->postSearch->remove($post);
        PostCache::forgetFor($post);
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
