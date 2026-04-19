<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Jobs\SubmitPostUrlToIndexNowJob;
use App\Models\Post;

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

        $this->queueIndexNowIfConfigured($post);
    }

    /**
     * When a draft becomes published, `status` is dirty on update and appears in `wasChanged`.
     */
    public function saved(Post $post): void
    {
        if ($post->status !== PostStatus::Published) {
            return;
        }

        if (! $post->wasChanged('status')) {
            return;
        }

        $this->queueIndexNowIfConfigured($post);
    }

    private function queueIndexNowIfConfigured(Post $post): void
    {
        if (! filled(config('indexnow.key'))) {
            return;
        }

        SubmitPostUrlToIndexNowJob::dispatch((int) $post->getKey());
    }
}
