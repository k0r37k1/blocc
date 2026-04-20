<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\IndexNowClient;

class PostObserver
{
    /**
     * New posts inserted already as published never populate `wasChanged('status')` on `saved`
     * (Laravel does not sync insert dirty attributes into `changes`), so we handle that here.
     */
    public function created(Post $post): void
    {
        if ($post->status !== PostStatus::Published) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    public function updated(Post $post): void
    {
        if ($post->status !== PostStatus::Published) {
            return;
        }

        $this->submitToIndexNow($post);
    }

    private function submitToIndexNow(Post $post): void
    {
        if (! filled(config('indexnow.key'))) {
            return;
        }

        app(IndexNowClient::class)->submitUrls([
            route('blog.show', $post, absolute: true),
        ]);
    }
}
